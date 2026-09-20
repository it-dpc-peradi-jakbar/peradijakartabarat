<?php

namespace Tests\Feature;

use App\Models\CandidateAdvocate;
use App\Models\JobPosting;
use App\Models\LawFirm;
use App\Models\SupervisingLawyer;
use App\Models\User;
use App\Support\WilayahHierarchy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsJakbarWilayah;
use Tests\TestCase;

class JobTransportTest extends TestCase
{
    use RefreshDatabase;
    use SeedsJakbarWilayah;

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
        $ca->matchedJobPostings()->attach([$withTransport->id, $withoutTransport->id]);

        $this->actingAs($user)
            ->get(route('candidate.lowongan'))
            ->assertOk()
            ->assertSee('Magang Dengan Transport')
            ->assertSee('Uang transport')
            ->assertSee('Magang Tanpa Transport');

        $ca->matchedJobPostings()->sync([$withoutTransport->id]);

        $this->actingAs($user)
            ->get(route('candidate.lowongan'))
            ->assertOk()
            ->assertSee('Magang Tanpa Transport')
            ->assertDontSee('Uang transport');
    }

    public function test_firm_can_update_own_posting_but_not_another_firms(): void
    {
        $this->seedJakbarWilayah();
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
            ->from(route('firm.lowongan.edit', $own))
            ->patch(route('firm.lowongan.update', $own), $this->updatePayload($own, [
                'title' => 'Lowongan Direvisi',
                'provides_transport' => '1',
                'quota' => 8,
            ]))
            ->assertRedirect(route('firm.lowongan'));

        $own->refresh();
        $this->assertSame('Lowongan Direvisi', $own->title);
        $this->assertSame(8, $own->quota);
        $this->assertTrue($own->provides_transport);

        $this->actingAs($firmUser)
            ->get(route('firm.lowongan.edit', $other))
            ->assertForbidden();

        $this->actingAs($firmUser)
            ->patch(route('firm.lowongan.update', $other), $this->updatePayload($other, ['provides_transport' => '1']))
            ->assertForbidden();
        $this->assertFalse($other->fresh()->provides_transport);
    }

    public function test_firm_cannot_create_a_job_posting(): void
    {
        $this->seedJakbarWilayah();
        $own = $this->verifiedJobPosting('Kantor Sendiri', 'Lowongan Sendiri', false);
        $firmUser = User::factory()->create(['role' => 'law_firm']);
        SupervisingLawyer::create([
            'law_firm_id' => $own->law_firm_id,
            'user_id' => $firmUser->id,
            'name' => 'Pendamping Uji',
            'bar_membership_number' => 'KTA-TEST-T2',
            'bar_membership_active' => true,
            'years_of_experience' => 5,
        ]);

        $this->actingAs($firmUser)
            ->get('/firm/lowongan/create')
            ->assertNotFound();

        $this->actingAs($firmUser)
            ->post('/firm/lowongan', $this->updatePayload($own))
            ->assertMethodNotAllowed();

        $this->assertSame(1, JobPosting::count());
    }

    /** @param  array<string, mixed>  $overrides */
    private function updatePayload(JobPosting $jobPosting, array $overrides = []): array
    {
        return array_merge([
            'title' => $jobPosting->title,
            'description' => $jobPosting->description,
            'practice_areas' => implode(', ', $jobPosting->practice_areas ?? []),
            'quota' => $jobPosting->quota,
            'status' => $jobPosting->status,
            'provides_transport' => $jobPosting->provides_transport ? '1' : '0',
            'provinsi_kode' => WilayahHierarchy::JAKARTA_PROVINSI,
            'kabupaten_kota_kode' => WilayahHierarchy::JAKARTA_BARAT_KOTA,
        ], $overrides);
    }

    private function verifiedJobPosting(string $firmName, string $title, bool $providesTransport): JobPosting
    {
        $firm = LawFirm::create([
            'name' => $firmName,
            'verification_status' => 'VERIFIED',
            'verified_at' => now(),
            ...$this->jakbarPayload(),
        ]);

        return JobPosting::create([
            'law_firm_id' => $firm->id,
            'title' => $title,
            'description' => 'Deskripsi',
            'practice_areas' => ['Litigasi'],
            'quota' => 5,
            'status' => 'ACTIVE',
            'provides_transport' => $providesTransport,
            'kabupaten_kota_kode' => WilayahHierarchy::JAKARTA_BARAT_KOTA,
        ]);
    }
}
