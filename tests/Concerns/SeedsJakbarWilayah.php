<?php

namespace Tests\Concerns;

use App\Models\Wilayah;
use App\Support\WilayahHierarchy;

trait SeedsJakbarWilayah
{
    protected function seedJakbarWilayah(): void
    {
        Wilayah::upsert([
            ['kode' => '31', 'nama' => 'DKI Jakarta', 'level' => 'provinsi'],
            ['kode' => '31.73', 'nama' => 'Kota Jakarta Barat', 'level' => 'kabupaten_kota'],
            ['kode' => '31.73.05', 'nama' => 'Kebon Jeruk', 'level' => 'kecamatan'],
            ['kode' => '32', 'nama' => 'Jawa Barat', 'level' => 'provinsi'],
            ['kode' => '32.01', 'nama' => 'Kabupaten Bogor', 'level' => 'kabupaten_kota'],
            ['kode' => '32.01.01', 'nama' => 'Nanggung', 'level' => 'kecamatan'],
        ], ['kode'], ['nama', 'level']);
    }

    /**
     * @return array<string, string>
     */
    protected function jakbarPayload(): array
    {
        return WilayahHierarchy::jakbar();
    }
}
