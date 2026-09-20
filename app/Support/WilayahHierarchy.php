<?php

namespace App\Support;

use App\Models\Wilayah;
use Illuminate\Validation\Rule;

final class WilayahHierarchy
{
    public const JAKARTA_PROVINSI = '31';

    public const JAKARTA_BARAT_KOTA = '31.73';

    public const KEBON_JERUK = '31.73.05';

    /**
     * @return array<string, mixed>
     */
    public static function jakbar(): array
    {
        return [
            'provinsi_kode' => self::JAKARTA_PROVINSI,
            'kabupaten_kota_kode' => self::JAKARTA_BARAT_KOTA,
            'kecamatan_kode' => self::KEBON_JERUK,
        ];
    }

    /**
     * @return array<string, list<mixed>>
     */
    public static function rules(): array
    {
        return [
            'provinsi_kode' => ['required', 'string', 'size:2', Rule::exists('wilayah', 'kode')->where('level', 'provinsi')],
            'kabupaten_kota_kode' => ['required', 'string', 'size:5', Rule::exists('wilayah', 'kode')->where('level', 'kabupaten_kota')],
            'kecamatan_kode' => ['required', 'string', 'size:8', Rule::exists('wilayah', 'kode')->where('level', 'kecamatan')],
        ];
    }

    public static function isConsistent(?string $provinsi, ?string $kabupaten, ?string $kecamatan): bool
    {
        if (! is_string($provinsi) || ! is_string($kabupaten) || ! is_string($kecamatan)) {
            return false;
        }

        return str_starts_with($kabupaten, $provinsi.'.')
            && str_starts_with($kecamatan, $kabupaten.'.')
            && Wilayah::query()->whereIn('kode', [$provinsi, $kabupaten, $kecamatan])->count() === 3;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public static function kabupatenRules(): array
    {
        return [
            'provinsi_kode' => ['required', 'string', 'size:2', Rule::exists('wilayah', 'kode')->where('level', 'provinsi')],
            'kabupaten_kota_kode' => ['required', 'string', 'size:5', Rule::exists('wilayah', 'kode')->where('level', 'kabupaten_kota')],
        ];
    }

    public static function kabupatenIsConsistent(?string $provinsi, ?string $kabupaten): bool
    {
        if (! is_string($provinsi) || ! is_string($kabupaten)) {
            return false;
        }

        return str_starts_with($kabupaten, $provinsi.'.')
            && Wilayah::query()->whereIn('kode', [$provinsi, $kabupaten])->count() === 2;
    }

    /**
     * @param  array<string, string>|null  $names
     */
    public static function label(?string $provinsi, ?string $kabupaten, ?string $kecamatan, ?array $names = null): string
    {
        $names ??= Wilayah::namaMap([$kecamatan, $kabupaten, $provinsi]);

        return implode(' · ', array_filter([
            $names[$kecamatan] ?? null,
            $names[$kabupaten] ?? null,
            $names[$provinsi] ?? null,
        ])) ?: '—';
    }
}
