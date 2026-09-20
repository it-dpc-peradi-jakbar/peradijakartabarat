<?php

namespace Tests\Feature\Firm;

use App\Models\CandidateAdvocate;
use App\Models\InternshipApplication;
use App\Models\JobPosting;
use App\Models\LawFirm;
use App\Models\SupervisingLawyer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PelamarMatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_firm_pelamar_list_only_includes_matched_candidates(): void
    {
        $firm = LawFirm::create([
            'name' => 'Kantor Pelamar',
            'verification_status' => 'VERIFIED',
            'verified_at' => now(),
        ]);
        $firmUser = User::factory()->create(['role' => 'law_firm']);
        SupervisingLawyer::create([
            'law_firm_id' => $firm->id,
            'user_id' => $firmUser->id,
            'name' => 'Pendamping Uji',
            'bar_membership_number' => 'KTA-TEST-1',
            'bar_membership_active' => true,
            'years_of_experience' => 5,
        ]);
        $job = JobPosting::create([
            'law_firm_id' => $firm->id,
            'title' => 'Magang Uji',
            'description' => 'Deskripsi',
            'practice_areas' => ['Litigasi'],
            'quota' => 5,
            'status' => 'ACTIVE',
        ]);

        $matchedUser = User::factory()->create(['role' => 'calon_advokat', 'name' => 'Calon Matched']);
        $matched = CandidateAdvocate::create([
            'user_id' => $matchedUser->id,
            'candidate_code' => 'CA-2026-0301',
            'verification_status' => 'VERIFIED',
        ]);
        $matched->matchedLawFirms()->attach($firm->id);
        InternshipApplication::create([
            'candidate_advocate_id' => $matched->id,
            'job_posting_id' => $job->id,
            'status' => InternshipApplication::STATUS_SUBMITTED,
            'applied_on' => now(),
        ]);

        $otherUser = User::factory()->create(['role' => 'calon_advokat', 'name' => 'Calon Unmatched']);
        $other = CandidateAdvocate::create([
            'user_id' => $otherUser->id,
            'candidate_code' => 'CA-2026-0302',
            'verification_status' => 'VERIFIED',
        ]);
        InternshipApplication::create([
            'candidate_advocate_id' => $other->id,
            'job_posting_id' => $job->id,
            'status' => InternshipApplication::STATUS_SUBMITTED,
            'applied_on' => now(),
        ]);

        $this->actingAs($firmUser)
            ->get(route('firm.pelamar'))
            ->assertOk()
            ->assertSee('Calon Matched')
            ->assertDontSee('Calon Unmatched');
    }
}
