<?php

namespace Tests\Feature\Candidate;

use App\Models\CandidateAdvocate;
use App\Models\InternshipApplication;
use App\Models\JobPosting;
use App\Models\LawFirm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternshipApplicationTest extends TestCase
{
    use RefreshDatabase;

    private function verifiedJobPosting(): JobPosting
    {
        $firm = LawFirm::create([
            'name' => 'Kantor Uji',
            'verification_status' => 'VERIFIED',
            'verified_at' => now(),
        ]);

        return JobPosting::create([
            'law_firm_id' => $firm->id,
            'title' => 'Magang Uji',
            'description' => 'Deskripsi',
            'practice_areas' => ['Litigasi'],
            'quota' => 5,
            'status' => 'ACTIVE',
        ]);
    }

    public function test_unverified_candidate_cannot_submit_internship_application(): void
    {
        $user = User::factory()->create(['role' => 'calon_advokat']);
        $ca = CandidateAdvocate::create([
            'user_id' => $user->id,
            'candidate_code' => 'CA-2026-0099',
            'membership_status' => 'ACTIVE',
            'verification_status' => 'PENDING',
        ]);
        $jobPosting = $this->verifiedJobPosting();
        $ca->matchedJobPostings()->attach($jobPosting->id);

        $response = $this->actingAs($user)->post(route('candidate.lowongan.lamar', $jobPosting));

        $response->assertSessionHasErrors('lamaran');
        $this->assertSame(0, InternshipApplication::count());
    }

    public function test_verified_candidate_can_submit_internship_application(): void
    {
        $user = User::factory()->create(['role' => 'calon_advokat']);
        $ca = CandidateAdvocate::create([
            'user_id' => $user->id,
            'candidate_code' => 'CA-2026-0100',
            'membership_status' => 'ACTIVE',
            'verification_status' => 'VERIFIED',
        ]);
        $jobPosting = $this->verifiedJobPosting();
        $ca->matchedJobPostings()->attach($jobPosting->id);

        $response = $this->actingAs($user)->post(route('candidate.lowongan.lamar', $jobPosting));

        $response->assertRedirect();
        $this->assertTrue(
            $ca->fresh()->internshipApplications()->where('job_posting_id', $jobPosting->id)->exists()
        );
    }

    public function test_unmatched_candidate_cannot_apply_and_sees_empty_lowongan(): void
    {
        $user = User::factory()->create(['role' => 'calon_advokat']);
        CandidateAdvocate::create([
            'user_id' => $user->id,
            'candidate_code' => 'CA-2026-0101',
            'membership_status' => 'ACTIVE',
            'verification_status' => 'VERIFIED',
        ]);
        $jobPosting = $this->verifiedJobPosting();

        $this->actingAs($user)
            ->get(route('candidate.lowongan'))
            ->assertOk()
            ->assertSee('Menunggu pencocokan Admin DPC')
            ->assertDontSee('Kantor Uji');

        $this->actingAs($user)
            ->post(route('candidate.lowongan.lamar', $jobPosting))
            ->assertForbidden();
        $this->assertSame(0, InternshipApplication::count());
    }
}
