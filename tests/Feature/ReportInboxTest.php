<?php

namespace Tests\Feature;

use App\Models\CandidateAdvocate;
use App\Models\InternshipApplication;
use App\Models\JobPosting;
use App\Models\LawFirm;
use App\Models\Report;
use App\Models\SupervisingLawyer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportInboxTest extends TestCase
{
    use RefreshDatabase;

    public function test_calon_cannot_report_unmatched_firm(): void
    {
        [$user, $ca] = $this->makeCalon();
        $matched = $this->verifiedFirm('Kantor Match');
        $unmatched = $this->verifiedFirm('Kantor Lain');
        $ca->matchedJobPostings()->attach($this->jobFor($matched)->id);

        $this->actingAs($user)
            ->post(route('candidate.report.store'), [
                'law_firm_id' => $unmatched->id,
                'body' => '<p>Kendala di kantor lain</p>',
            ])
            ->assertForbidden();

        $this->assertSame(0, Report::count());
    }

    public function test_calon_can_report_matched_firm_and_admin_can_read_it(): void
    {
        [$user, $ca] = $this->makeCalon('Calon Lapor');
        $firm = $this->verifiedFirm('Kantor Match');
        $ca->matchedJobPostings()->attach($this->jobFor($firm)->id);

        $this->actingAs($user)
            ->post(route('candidate.report.store'), [
                'law_firm_id' => $firm->id,
                'body' => '<p>Kendala kuota magang</p>',
            ])
            ->assertRedirect(route('candidate.report.create'));

        $report = Report::first();
        $this->assertNotNull($report);
        $this->assertSame($ca->id, $report->candidate_advocate_id);
        $this->assertSame($firm->id, $report->law_firm_id);
        $this->assertStringContainsString('Kendala kuota magang', $report->body);

        $admin = User::factory()->create(['role' => 'admin_dpc']);
        $this->actingAs($admin)
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('Calon Lapor')
            ->assertSee('Kantor Match');

        $this->actingAs($admin)
            ->get(route('admin.reports.show', $report))
            ->assertOk()
            ->assertSee('Kendala kuota magang');

        $this->assertNotNull($report->fresh()->read_at);

        $this->actingAs($user)
            ->get(route('admin.reports.index'))
            ->assertForbidden();
    }

    public function test_firm_cannot_report_calon_unless_applied_and_matched(): void
    {
        [$firmUser, $firm] = $this->makeFirm();
        $job = JobPosting::create([
            'law_firm_id' => $firm->id,
            'title' => 'Magang Uji',
            'description' => 'Deskripsi',
            'practice_areas' => ['Litigasi'],
            'quota' => 5,
            'status' => 'ACTIVE',
        ]);

        [, $unmatched] = $this->makeCalon('Calon Unmatched');
        InternshipApplication::create([
            'candidate_advocate_id' => $unmatched->id,
            'job_posting_id' => $job->id,
            'status' => InternshipApplication::STATUS_SUBMITTED,
            'applied_on' => now(),
        ]);

        [, $matchedNoApply] = $this->makeCalon('Calon Matched');
        $matchedNoApply->matchedJobPostings()->attach($job->id);

        $this->actingAs($firmUser)
            ->post(route('firm.report.store'), [
                'candidate_advocate_id' => $unmatched->id,
                'body' => '<p>Laporan unmatched</p>',
            ])
            ->assertForbidden();

        $this->actingAs($firmUser)
            ->post(route('firm.report.store'), [
                'candidate_advocate_id' => $matchedNoApply->id,
                'body' => '<p>Laporan tanpa lamaran</p>',
            ])
            ->assertForbidden();

        $this->assertSame(0, Report::count());

        $matchedNoApply->internshipApplications()->create([
            'job_posting_id' => $job->id,
            'status' => InternshipApplication::STATUS_SUBMITTED,
            'applied_on' => now(),
        ]);

        $this->actingAs($firmUser)
            ->post(route('firm.report.store'), [
                'candidate_advocate_id' => $matchedNoApply->id,
                'body' => '<p>Laporan sah</p>',
            ])
            ->assertRedirect(route('firm.report.create'));

        $this->assertSame(1, Report::count());
    }

    /** @return array{0: User, 1: CandidateAdvocate} */
    private function makeCalon(string $name = 'Calon Uji'): array
    {
        $user = User::factory()->create(['role' => 'calon_advokat', 'name' => $name]);
        $ca = CandidateAdvocate::create([
            'user_id' => $user->id,
            'candidate_code' => 'CA-R-'.uniqid(),
            'verification_status' => 'VERIFIED',
        ]);

        return [$user, $ca];
    }

    /** @return array{0: User, 1: LawFirm} */
    private function makeFirm(): array
    {
        $firm = $this->verifiedFirm('Kantor Pelapor');
        $firmUser = User::factory()->create(['role' => 'law_firm']);
        SupervisingLawyer::create([
            'law_firm_id' => $firm->id,
            'user_id' => $firmUser->id,
            'name' => 'Pendamping Uji',
            'bar_membership_number' => 'KTA-TEST-R1',
            'bar_membership_active' => true,
            'years_of_experience' => 5,
        ]);

        return [$firmUser, $firm];
    }

    private function verifiedFirm(string $name): LawFirm
    {
        return LawFirm::create([
            'name' => $name,
            'verification_status' => 'VERIFIED',
            'verified_at' => now(),
        ]);
    }

    private function jobFor(LawFirm $firm): JobPosting
    {
        return JobPosting::create([
            'law_firm_id' => $firm->id,
            'title' => 'Magang '.$firm->name,
            'description' => 'Deskripsi',
            'practice_areas' => ['Litigasi'],
            'quota' => 5,
            'status' => 'ACTIVE',
        ]);
    }
}
