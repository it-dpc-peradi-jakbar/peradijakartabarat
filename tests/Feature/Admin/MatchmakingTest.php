<?php

namespace Tests\Feature\Admin;

use App\Models\CandidateAdvocate;
use App\Models\LawFirm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsJakbarWilayah;
use Tests\TestCase;

class MatchmakingTest extends TestCase
{
    use RefreshDatabase;
    use SeedsJakbarWilayah;

    public function test_admin_can_match_verified_candidate_to_verified_firms(): void
    {
        $this->seedJakbarWilayah();
        $admin = User::factory()->create(['role' => 'admin_dpc']);
        $user = User::factory()->create(['role' => 'calon_advokat', 'name' => 'Calon Match']);
        $ca = CandidateAdvocate::create([
            'user_id' => $user->id,
            'candidate_code' => 'CA-2026-0200',
            'verification_status' => 'VERIFIED',
            ...$this->jakbarPayload(),
        ]);
        $firm = LawFirm::create([
            'name' => 'Kantor Match',
            'verification_status' => 'VERIFIED',
            'verified_at' => now(),
            ...$this->jakbarPayload(),
        ]);
        LawFirm::create([
            'name' => 'Kantor Pending',
            'verification_status' => 'PENDING',
            ...$this->jakbarPayload(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.matchmaking.edit', $ca))
            ->assertOk()
            ->assertSee('Cari nama atau wilayah kantor')
            ->assertSee('Kota sama')
            ->assertSee('Kantor Match')
            ->assertDontSee('Kantor Pending');

        $this->actingAs($admin)
            ->put(route('admin.matchmaking.update', $ca), [
                'law_firm_ids' => [$firm->id],
            ])
            ->assertRedirect(route('admin.matchmaking'));

        $this->assertTrue($ca->fresh()->isMatchedToFirm($firm->id));
        $this->assertSame(1, $ca->matchedLawFirms()->count());
    }

    public function test_non_admin_cannot_open_matchmaking(): void
    {
        $user = User::factory()->create(['role' => 'calon_advokat']);

        $this->actingAs($user)
            ->get(route('admin.matchmaking'))
            ->assertForbidden();
    }
}
