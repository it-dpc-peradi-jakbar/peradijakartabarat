<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\CandidateAdvocate;
use App\Models\LawFirm;
use App\Models\SupervisingLawyer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_publish_banner_for_calon_and_firm_does_not_see_it(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin_dpc']);
        [$calon] = $this->makeCalon();
        [$firmUser] = $this->makeFirmUser();

        $this->actingAs($admin)
            ->post(route('admin.banners.store'), [
                'title' => 'Pengumuman Calon',
                'target' => Banner::TARGET_CALON,
                'body' => '<p>Isi untuk calon</p>',
                'published' => '1',
                'image' => UploadedFile::fake()->create('hero.jpg', 80, 'image/jpeg'),
            ])
            ->assertRedirect(route('admin.banners.index'));

        $banner = Banner::first();
        $this->assertNotNull($banner?->published_at);
        $this->assertNotNull($banner->image_path);
        Storage::disk('public')->assertExists($banner->image_path);

        $this->actingAs($calon)
            ->get(route('candidate.dashboard'))
            ->assertOk()
            ->assertSee('Pengumuman Calon')
            ->assertSee('/storage/banners/');

        $this->actingAs($calon)
            ->get(route('candidate.lowongan'))
            ->assertOk()
            ->assertDontSee('Pengumuman Calon');

        $this->actingAs($firmUser)
            ->get(route('firm.dashboard'))
            ->assertOk()
            ->assertDontSee('Pengumuman Calon');
    }

    public function test_draft_other_target_and_expired_banners_are_hidden(): void
    {
        $admin = User::factory()->create(['role' => 'admin_dpc']);
        [$calon] = $this->makeCalon();

        Banner::create([
            'created_by_user_id' => $admin->id,
            'target' => Banner::TARGET_CALON,
            'title' => 'Draf Calon',
            'body' => '<p>Draf</p>',
            'published_at' => null,
        ]);
        Banner::create([
            'created_by_user_id' => $admin->id,
            'target' => Banner::TARGET_FIRM,
            'title' => 'Untuk Firm',
            'body' => '<p>Firm</p>',
            'published_at' => now(),
        ]);
        Banner::create([
            'created_by_user_id' => $admin->id,
            'target' => Banner::TARGET_CALON,
            'title' => 'Sudah Kedaluwarsa',
            'body' => '<p>Lama</p>',
            'published_at' => now()->subDay(),
            'expires_at' => now()->subHour(),
        ]);
        Banner::create([
            'created_by_user_id' => $admin->id,
            'target' => Banner::TARGET_CALON,
            'title' => 'Masih Berlaku',
            'body' => '<p>Aktif</p>',
            'published_at' => now(),
            'expires_at' => now()->addDay(),
        ]);

        $this->actingAs($calon)
            ->get(route('candidate.dashboard'))
            ->assertOk()
            ->assertSee('Masih Berlaku')
            ->assertDontSee('Draf Calon')
            ->assertDontSee('Untuk Firm')
            ->assertDontSee('Sudah Kedaluwarsa');
    }

    public function test_login_does_not_show_public_banner(): void
    {
        $admin = User::factory()->create(['role' => 'admin_dpc']);
        Banner::create([
            'created_by_user_id' => $admin->id,
            'target' => Banner::TARGET_PUBLIC,
            'title' => 'Banner Publik Rahasia',
            'body' => '<p>Publik</p>',
            'published_at' => now(),
        ]);

        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('Banner Publik Rahasia');
    }

    public function test_non_admin_cannot_create_banner(): void
    {
        [$calon] = $this->makeCalon();

        $this->actingAs($calon)
            ->post(route('admin.banners.store'), [
                'title' => 'Tidak Boleh',
                'target' => Banner::TARGET_CALON,
                'body' => '<p>x</p>',
                'published' => '1',
            ])
            ->assertForbidden();

        $this->assertSame(0, Banner::count());
    }

    /** @return array{0: User} */
    private function makeCalon(): array
    {
        $user = User::factory()->create(['role' => 'calon_advokat']);
        CandidateAdvocate::create([
            'user_id' => $user->id,
            'candidate_code' => 'CA-B-'.uniqid(),
            'verification_status' => 'VERIFIED',
        ]);

        return [$user];
    }

    /** @return array{0: User} */
    private function makeFirmUser(): array
    {
        $firm = LawFirm::create([
            'name' => 'Kantor Banner',
            'verification_status' => 'VERIFIED',
            'verified_at' => now(),
        ]);
        $user = User::factory()->create(['role' => 'law_firm']);
        SupervisingLawyer::create([
            'law_firm_id' => $firm->id,
            'user_id' => $user->id,
            'name' => 'Pendamping Banner',
            'bar_membership_number' => 'KTA-BANNER-1',
            'bar_membership_active' => true,
            'years_of_experience' => 5,
        ]);

        return [$user];
    }
}
