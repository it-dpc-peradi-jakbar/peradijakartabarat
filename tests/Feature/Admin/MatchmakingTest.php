<?php

namespace Tests\Feature\Admin;

use App\Models\CandidateAdvocate;
use App\Models\JobPosting;
use App\Models\LawFirm;
use App\Models\User;
use App\Support\WilayahHierarchy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsJakbarWilayah;
use Tests\TestCase;

class MatchmakingTest extends TestCase
{
    use RefreshDatabase;
    use SeedsJakbarWilayah;

    public function test_admin_can_match_verified_candidate_to_verified_jobs_including_outside_prefs(): void
    {
        $this->seedJakbarWilayah();
        $admin = User::factory()->create(['role' => 'admin_dpc']);
        $user = User::factory()->create(['role' => 'calon_advokat', 'name' => 'Calon Match']);
        $ca = CandidateAdvocate::create([
            'user_id' => $user->id,
            'candidate_code' => 'CA-2026-0200',
            'verification_status' => 'VERIFIED',
            'wants_transport' => true,
            ...$this->jakbarPayload(),
        ]);
        $firm = LawFirm::create([
            'name' => 'Kantor Match',
            'verification_status' => 'VERIFIED',
            'verified_at' => now(),
            ...$this->jakbarPayload(),
        ]);
        $fitJob = JobPosting::create([
            'law_firm_id' => $firm->id,
            'title' => 'Lowongan Cocok',
            'quota' => 5,
            'status' => 'ACTIVE',
            'provides_transport' => true,
            'kabupaten_kota_kode' => WilayahHierarchy::JAKARTA_BARAT_KOTA,
        ]);
        $outsideJob = JobPosting::create([
            'law_firm_id' => $firm->id,
            'title' => 'Lowongan Luar',
            'quota' => 5,
            'status' => 'ACTIVE',
            'provides_transport' => false,
            'kabupaten_kota_kode' => '32.01',
        ]);
        LawFirm::create([
            'name' => 'Kantor Pending',
            'verification_status' => 'PENDING',
            ...$this->jakbarPayload(),
        ]);

        $this->assertFalse($ca->jobFitsPreferences($outsideJob));

        $this->actingAs($admin)
            ->get(route('admin.matchmaking.edit', $ca))
            ->assertOk()
            ->assertSee('Cari judul, kantor, atau wilayah lowongan')
            ->assertSee('Di luar preferensi')
            ->assertSee('Lowongan Cocok')
            ->assertSee('Lowongan Luar')
            ->assertDontSee('Kantor Pending');

        $this->actingAs($admin)
            ->put(route('admin.matchmaking.update', $ca), [
                'job_posting_ids' => [$fitJob->id, $outsideJob->id],
            ])
            ->assertRedirect(route('admin.matchmaking'));

        $this->assertTrue($ca->fresh()->isMatchedToJob($fitJob->id));
        $this->assertTrue($ca->fresh()->isMatchedToJob($outsideJob->id));
        $this->assertSame(2, $ca->matchedJobPostings()->count());
    }

    public function test_non_admin_cannot_open_matchmaking(): void
    {
        $user = User::factory()->create(['role' => 'calon_advokat']);

        $this->actingAs($user)
            ->get(route('admin.matchmaking'))
            ->assertForbidden();
    }
}
