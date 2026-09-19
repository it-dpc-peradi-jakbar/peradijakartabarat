<?php

namespace Tests\Feature\Auth;

use App\Models\LawFirm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsJakbarWilayah;
use Tests\TestCase;

class LawFirmRegistrationTest extends TestCase
{
    use RefreshDatabase;
    use SeedsJakbarWilayah;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedJakbarWilayah();
    }

    public function test_law_firm_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register/law-firm');

        $response->assertStatus(200);
    }

    public function test_new_law_firms_can_register(): void
    {
        $response = $this->post('/register/law-firm', [
            'firm_name' => 'Kantor Uji Coba',
            'firm_address' => 'Jl. Contoh No. 1, Jakarta Barat',
            'ministry_registration_number' => 'AHU-TEST-001',
            'name' => 'Budi Advokat, S.H.',
            'email' => 'firm@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'bar_membership_number' => 'KTA-DPC-JB-99999',
            'years_of_experience' => 5,
            ...$this->jakbarPayload(),
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'firm@example.com')->first();
        $this->assertSame('law_firm', $user->role);
        $this->assertNotNull($user->supervisingLawyer);
        $firm = $user->supervisingLawyer->lawFirm;
        $this->assertSame('Kantor Uji Coba', $firm->name);
        $this->assertSame('PENDING', $firm->verification_status);
        $this->assertSame('31.73.05', $firm->kecamatan_kode);
        $this->assertSame(3, LawFirm::first()->checklistItems()->count());
    }
}
