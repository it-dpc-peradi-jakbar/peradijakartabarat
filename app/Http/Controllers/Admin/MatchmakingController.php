<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CandidateAdvocate;
use App\Models\JobPosting;
use App\Models\LawFirm;
use App\Models\Wilayah;
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
            ->with(['user', 'matchedJobPostings.lawFirm'])
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($inner) use ($keyword) {
                    $inner->where('candidate_code', 'like', "%{$keyword}%")
                        ->orWhereHas('user', fn ($u) => $u
                            ->where('name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%"));
                });
            })
            ->when($filter === 'unmatched', fn ($q) => $q->whereDoesntHave('matchedJobPostings'))
            ->when($filter === 'matched', fn ($q) => $q->whereHas('matchedJobPostings'))
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            ['value' => (string) CandidateAdvocate::where('verification_status', 'VERIFIED')->count(), 'label' => 'Calon terverifikasi'],
            ['value' => (string) CandidateAdvocate::where('verification_status', 'VERIFIED')->whereDoesntHave('matchedJobPostings')->count(), 'label' => 'Belum di-match'],
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

        $candidateAdvocate->load(['user', 'matchedJobPostings']);
        $selectedIds = $candidateAdvocate->matchedJobPostings->pluck('id')->all();

        $jobs = JobPosting::query()
            ->where('status', 'ACTIVE')
            ->whereHas('lawFirm', fn ($q) => $q->where('verification_status', 'VERIFIED'))
            ->with('lawFirm')
            ->orderBy('title')
            ->get()
            ->sortBy(function (JobPosting $job) use ($candidateAdvocate, $selectedIds) {
                $selected = in_array($job->id, $selectedIds, true) ? '0' : '1';
                $fit = $candidateAdvocate->jobFitsPreferences($job) ? '0' : '1';

                return $selected.$fit.$job->lawFirm->name.$job->title;
            })
            ->values();

        $wilayahNames = Wilayah::namaMap($jobs->flatMap(fn (JobPosting $job) => [
            $job->kabupaten_kota_kode,
            $job->kabupaten_kota_kode ? substr($job->kabupaten_kota_kode, 0, 2) : null,
            $candidateAdvocate->kabupaten_kota_kode,
            $candidateAdvocate->provinsi_kode,
        ])->all());

        return view('admin.matchmaking-edit', [
            'ca' => $candidateAdvocate,
            'jobs' => $jobs,
            'wilayahNames' => $wilayahNames,
            'selectedIds' => $selectedIds,
        ]);
    }

    public function update(Request $request, CandidateAdvocate $candidateAdvocate): RedirectResponse
    {
        abort_unless($candidateAdvocate->verification_status === 'VERIFIED', 404);

        $data = $request->validate([
            'job_posting_ids' => ['nullable', 'array'],
            'job_posting_ids.*' => ['integer', 'exists:job_postings,id'],
        ]);

        $jobIds = collect($data['job_posting_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $allowed = JobPosting::query()
            ->where('status', 'ACTIVE')
            ->whereHas('lawFirm', fn ($q) => $q->where('verification_status', 'VERIFIED'))
            ->whereIn('id', $jobIds)
            ->pluck('id');

        $sync = $allowed->mapWithKeys(fn ($id) => [$id => ['matched_by' => $request->user()->id]])->all();
        $candidateAdvocate->matchedJobPostings()->sync($sync);

        return redirect()
            ->route('admin.matchmaking')
            ->with('status', 'Pencocokan '.$candidateAdvocate->user->name.' disimpan.');
    }
}
