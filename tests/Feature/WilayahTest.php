<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsJakbarWilayah;
use Tests\TestCase;

class WilayahTest extends TestCase
{
    use RefreshDatabase;
    use SeedsJakbarWilayah;

    public function test_guest_can_list_provinces_and_children(): void
    {
        $this->seedJakbarWilayah();

        $this->getJson(route('wilayah.provinsi'))
            ->assertOk()
            ->assertJsonFragment(['kode' => '31', 'nama' => 'DKI Jakarta']);

        $this->getJson(route('wilayah.index', ['parent' => '31']))
            ->assertOk()
            ->assertJsonFragment(['kode' => '31.73', 'nama' => 'Kota Jakarta Barat'])
            ->assertJsonMissing(['kode' => '32.01']);
    }
}
