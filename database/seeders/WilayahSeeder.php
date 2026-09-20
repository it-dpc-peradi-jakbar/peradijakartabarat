<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class WilayahSeeder extends Seeder
{
    public function run(): void
    {
        Artisan::call('wilayah:import');
    }
}
