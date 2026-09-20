<?php

namespace Tests\Feature;

use App\Models\CandidateAdvocate;
use App\Models\JobPosting;
use App\Models\LawFirm;
use App\Models\SupervisingLawyer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobTransportTest extends TestCase
{
    use RefreshDatabase;

    public function test_lowongan_shows_transport_tag_only_when_flag_is_true(): void
    {
        $user = User::factory()->create(['role' => 'calon_advokat']);
        $ca = CandidateAdvocate::create([
            'user_id' => $user->id,
            'candidate_code' => 'CA-2026-0400',
            'verification_status' => 'VERIFIED',
        ]);

        $withTransport = $this->verifiedJobPosting('Kantor Transport', 'Magang Dengan Transport', true);
        $withoutTransport = $this->verifiedJobPosting('Kantor Biasa', 'Magang Tanpa Transport', false);
        $ca->matchedLawFirms()->attach([$withTransport->law_firm_id, $withoutTransport->law_firm_id]);

        $this->actingAs($user)
            ->get(route('candidate.lowongan'))
            ->assertOk()
            ->assertSee('Magang Dengan Transport')
            ->assertSee('Uang transport')
            ->assertSee('Magang Tanpa Transport');

        $ca->matchedLawFirms()->sync([$withoutTransport->law_firm_id]);

        $this->actingAs($user)
            ->get(route('candidate.lowongan'))
            ->assertOk()
            ->assertSee('Magang Tanpa Transport')
            ->assertDontSee('Uang transport');
    }

    public function test_firm_can_toggle_own_posting_but_not_another_firms(): void
    {
        $own = $this->verifiedJobPosting('Kantor Sendiri', 'Lowongan Sendiri', false);
        $other = $this->verifiedJobPosting('Kantor Lain', 'Lowongan Lain', false);

        $firmUser = User::factory()->create(['role' => 'law_firm']);
        SupervisingLawyer::create([
            'law_firm_id' => $own->law_firm_id,
            'user_id' => $firmUser->id,
            'name' => 'Pendamping Uji',
            'bar_membership_number' => 'KTA-TEST-T1',
            'bar_membership_active' => true,
            'years_of_experience' => 5,
        ]);

        $this->actingAs($firmUser)
            ->patch(route('firm.lowongan.update', $own), ['provides_transport' => '1'])
            ->assertRedirect();
        $this->assertTrue($own->fresh()->provides_transport);

        $this->actingAs($firmUser)
            ->patch(route('firm.lowongan.update', $other), ['provides_transport' => '1'])
            ->assertForbidden();
        $this->assertFalse($other->fresh()->provides_transport);
    }

    private function verifiedJobPosting(string $firmName, string $title, bool $providesTransport): JobPosting
    {
        $firm = LawFirm::create([
            'name' => $firmName,
            'verification_status' => 'VERIFIED',
            'verified_at' => now(),
        ]);

        return JobPosting::create([
            'law_firm_id' => $firm->id,
            'title' => $title,
            'description' => 'Deskripsi',
            'practice_areas' => ['Litigasi'],
            'quota' => 5,
            'status' => 'ACTIVE',
            'provides_transport' => $providesTransport,
        ]);
    }
}
