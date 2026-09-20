<?php

namespace Tests\Feature\Candidate;

use App\Models\CandidateAdvocate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_can_store_global_work_reference(): void
    {
        $user = User::factory()->create(['role' => 'calon_advokat']);
        $ca = CandidateAdvocate::create([
            'user_id' => $user->id,
            'candidate_code' => 'CA-PREF-1',
            'verification_status' => 'VERIFIED',
        ]);

        $this->actingAs($user)
            ->patch(route('candidate.preferences.update'), [
                'work_reference_global' => '1',
                'wants_transport' => '1',
            ])
            ->assertRedirect(route('candidate.preferences.edit'));

        $ca->refresh();
        $this->assertSame([CandidateAdvocate::WORK_REFERENCE_GLOBAL], $ca->work_reference);
        $this->assertTrue($ca->wantsAnywhere());
        $this->assertTrue($ca->wants_transport);

        $this->actingAs($user)
            ->patch(route('candidate.preferences.update'), [
                'work_reference_global' => '0',
                'wants_transport' => '0',
            ])
            ->assertRedirect(route('candidate.preferences.edit'));

        $ca->refresh();
        $this->assertSame([], $ca->work_reference);
        $this->assertFalse($ca->wantsAnywhere());
        $this->assertFalse($ca->wants_transport);
    }
}
