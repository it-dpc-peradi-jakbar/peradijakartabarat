<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Wilayah extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $table = 'wilayah';

    protected $keyType = 'string';

    protected $primaryKey = 'kode';

    protected $fillable = [
        'kode',
        'nama',
        'level',
    ];

    public function scopeProvinsi(Builder $query): Builder
    {
        return $query->where('level', 'provinsi')->orderBy('kode');
    }

    public function scopeChildrenOf(Builder $query, string $parentKode): Builder
    {
        $parentLen = strlen($parentKode);
        $childLen = $parentLen === 2 ? 5 : 8;
        $childLevel = $parentLen === 2 ? 'kabupaten_kota' : 'kecamatan';

        return $query->where('level', $childLevel)
            ->where('kode', 'like', $parentKode.'.%')
            ->whereRaw('length(kode) = ?', [$childLen])
            ->orderBy('nama');
    }

    /**
     * @param  list<string|null>  $kodes
     * @return array<string, string>
     */
    public static function namaMap(array $kodes): array
    {
        $kodes = array_values(array_filter($kodes));
        if ($kodes === []) {
            return [];
        }

        return static::query()->whereIn('kode', $kodes)->pluck('nama', 'kode')->all();
    }
}
