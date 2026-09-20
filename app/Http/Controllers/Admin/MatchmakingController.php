<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CandidateAdvocate;
use App\Models\LawFirm;
use App\Models\Wilayah;
use App\Support\WilayahHierarchy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MatchmakingController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->query('filter', 'all');
        $keyword = trim((string) $request->query('q', ''));

        $candidates = CandidateAdvocate::query()
            ->where('verification_status', 'VERIFIED')
            ->with(['user', 'matchedLawFirms'])
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($inner) use ($keyword) {
                    $inner->where('candidate_code', 'like', "%{$keyword}%")
                        ->orWhereHas('user', fn ($u) => $u
                            ->where('name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%"));
                });
            })
            ->when($filter === 'unmatched', fn ($q) => $q->whereDoesntHave('matchedLawFirms'))
            ->when($filter === 'matched', fn ($q) => $q->whereHas('matchedLawFirms'))
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            ['value' => (string) CandidateAdvocate::where('verification_status', 'VERIFIED')->count(), 'label' => 'Calon terverifikasi'],
            ['value' => (string) CandidateAdvocate::where('verification_status', 'VERIFIED')->whereDoesntHave('matchedLawFirms')->count(), 'label' => 'Belum di-match'],
            ['value' => (string) LawFirm::where('verification_status', 'VERIFIED')->count(), 'label' => 'Kantor terverifikasi'],
        ];

        return view('admin.matchmaking', [
            'candidates' => $candidates,
            'filter' => in_array($filter, ['all', 'unmatched', 'matched'], true) ? $filter : 'all',
            'keyword' => $keyword,
            'stats' => $stats,
        ]);
    }

    public function edit(CandidateAdvocate $candidateAdvocate): View
    {
        abort_unless($candidateAdvocate->verification_status === 'VERIFIED', 404);

        $candidateAdvocate->load(['user', 'matchedLawFirms']);
        $selectedIds = $candidateAdvocate->matchedLawFirms->pluck('id')->all();
        $firms = LawFirm::where('verification_status', 'VERIFIED')
            ->orderBy('name')
            ->get()
            ->sortBy(fn (LawFirm $firm) => (in_array($firm->id, $selectedIds, true) ? '0' : '1').$firm->name)
            ->values();
        $wilayahNames = Wilayah::namaMap($firms->flatMap(fn (LawFirm $firm) => [
            $firm->kecamatan_kode,
            $firm->kabupaten_kota_kode,
            $firm->provinsi_kode,
        ])->all());

        return view('admin.matchmaking-edit', [
            'ca' => $candidateAdvocate,
            'firms' => $firms,
            'wilayahNames' => $wilayahNames,
            'selectedIds' => $selectedIds,
        ]);
    }

    public function update(Request $request, CandidateAdvocate $candidateAdvocate): RedirectResponse
    {
        abort_unless($candidateAdvocate->verification_status === 'VERIFIED', 404);

        $data = $request->validate([
            'law_firm_ids' => ['nullable', 'array'],
            'law_firm_ids.*' => ['integer', 'exists:law_firms,id'],
        ]);

        $firmIds = collect($data['law_firm_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $allowed = LawFirm::where('verification_status', 'VERIFIED')
            ->whereIn('id', $firmIds)
            ->pluck('id');

        $sync = $allowed->mapWithKeys(fn ($id) => [$id => ['matched_by' => $request->user()->id]])->all();
        $candidateAdvocate->matchedLawFirms()->sync($sync);

        return redirect()
            ->route('admin.matchmaking')
            ->with('status', 'Pencocokan '.$candidateAdvocate->user->name.' disimpan.');
    }
}
