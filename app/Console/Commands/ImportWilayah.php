<?php

namespace App\Console\Commands;

use App\Models\Wilayah;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportWilayah extends Command
{
    protected $signature = 'wilayah:import';

    protected $description = 'Import provinsi / kabupaten-kota / kecamatan from compact JSON (or extract from docs/wilayah).';

    public function handle(): int
    {
        $jsonPath = database_path('data/wilayah_prov_kab_kec.json');
        $sqlPath = base_path('docs/wilayah/db/wilayah.sql');

        if (! File::exists($jsonPath) && File::exists($sqlPath)) {
            $this->extractFromSql($sqlPath, $jsonPath);
        }

        if (! File::exists($jsonPath)) {
            $this->error('Missing '.$jsonPath.' (and no docs/wilayah/db/wilayah.sql to extract).');

            return self::FAILURE;
        }

        $rows = json_decode(File::get($jsonPath), true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($rows) || $rows === []) {
            $this->error('Wilayah JSON is empty.');

            return self::FAILURE;
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            Wilayah::upsert($chunk, ['kode'], ['nama', 'level']);
        }

        $this->info('Imported '.count($rows).' wilayah rows.');

        return self::SUCCESS;
    }

    private function extractFromSql(string $sqlPath, string $jsonPath): void
    {
        $this->info('Extracting compact wilayah dump from '.$sqlPath);
        $sql = File::get($sqlPath);
        if (! preg_match_all("/\('([^']+)','((?:\\\\'|[^'])*)'\)/", $sql, $matches, PREG_SET_ORDER)) {
            return;
        }

        $rows = [];
        foreach ($matches as $m) {
            $kode = $m[1];
            $nama = str_replace("\\'", "'", $m[2]);
            $len = strlen($kode);
            $level = match ($len) {
                2 => 'provinsi',
                5 => 'kabupaten_kota',
                8 => 'kecamatan',
                default => null,
            };
            if ($level === null) {
                continue;
            }
            $rows[] = ['kode' => $kode, 'nama' => trim($nama), 'level' => $level];
        }

        File::ensureDirectoryExists(dirname($jsonPath));
        File::put($jsonPath, json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
