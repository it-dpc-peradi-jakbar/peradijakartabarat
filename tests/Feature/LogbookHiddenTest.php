<?php

namespace Tests\Feature;

use App\Models\CandidateAdvocate;
use App\Models\LawFirm;
use App\Models\SupervisingLawyer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogbookHiddenTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_and_firm_logbook_routes_are_not_found(): void
    {
        $calon = User::factory()->create(['role' => 'calon_advokat']);
        CandidateAdvocate::create([
            'user_id' => $calon->id,
            'candidate_code' => 'CA-2026-0500',
            'verification_status' => 'VERIFIED',
        ]);

        $this->actingAs($calon)
            ->get(route('candidate.logbook'))
            ->assertNotFound();

        $firm = LawFirm::create([
            'name' => 'Kantor Logbook',
            'verification_status' => 'VERIFIED',
            'verified_at' => now(),
        ]);
        $firmUser = User::factory()->create(['role' => 'law_firm']);
        SupervisingLawyer::create([
            'law_firm_id' => $firm->id,
            'user_id' => $firmUser->id,
            'name' => 'Pendamping Uji',
            'bar_membership_number' => 'KTA-TEST-L1',
            'bar_membership_active' => true,
            'years_of_experience' => 5,
        ]);

        $this->actingAs($firmUser)
            ->get(route('firm.logbook'))
            ->assertNotFound();
    }
}
