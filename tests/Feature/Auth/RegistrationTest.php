<?php

namespace Tests\Feature\Auth;

use App\Models\CandidateAdvocate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsJakbarWilayah;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;
    use SeedsJakbarWilayah;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedJakbarWilayah();
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register/advocate-candidate');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register/advocate-candidate', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'address' => 'Jl. Panjang No. 12, Kebon Jeruk',
            ...$this->jakbarPayload(),
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('candidate.verification', absolute: false));

        $ca = CandidateAdvocate::where('user_id', User::where('email', 'test@example.com')->value('id'))->first();
        $this->assertSame('31', $ca->provinsi_kode);
        $this->assertSame('31.73', $ca->kabupaten_kota_kode);
        $this->assertSame('31.73.05', $ca->kecamatan_kode);
        $this->assertSame('Jl. Panjang No. 12, Kebon Jeruk', $ca->address);
    }

    public function test_registration_requires_consistent_wilayah(): void
    {
        $response = $this->from('/register/advocate-candidate')->post('/register/advocate-candidate', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'address' => 'Jl. Panjang No. 12, Kebon Jeruk',
            'provinsi_kode' => '31',
            'kabupaten_kota_kode' => '32.01',
            'kecamatan_kode' => '32.01.01',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('kecamatan_kode');
    }
}
