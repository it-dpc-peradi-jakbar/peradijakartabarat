<?php

namespace App\Http\Controllers;

use App\Models\Wilayah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WilayahController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $parent = $request->query('parent');

        $query = $parent
            ? Wilayah::query()->childrenOf((string) $parent)
            : Wilayah::query()->provinsi();

        return response()->json(
            $query->get(['kode', 'nama'])->map(fn (Wilayah $w) => [
                'kode' => $w->kode,
                'nama' => $w->nama,
            ])->values()
        );
    }
}
