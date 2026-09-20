<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\InternshipApplication;
use App\Models\JobPosting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LowonganController extends Controller
{
    public function index(Request $request): View
    {
        $ca = Auth::user()->candidateAdvocate;

        $bidangFilter = ['Semua bidang', 'Litigasi', 'Korporasi', 'Prodeo'];
        $filter = in_array($request->query('bidang'), $bidangFilter, true) ? $request->query('bidang') : 'Semua bidang';

        $matchedFirmIds = $ca->matchedLawFirms()->pluck('law_firms.id');
        $awaitingMatch = $matchedFirmIds->isEmpty();

        $jobPostings = $awaitingMatch
            ? collect()
            : JobPosting::query()
                ->where('status', 'ACTIVE')
                ->whereIn('law_firm_id', $matchedFirmIds)
                ->whereHas('lawFirm', fn ($q) => $q->where('verification_status', 'VERIFIED'))
                ->with('lawFirm')
                ->when($filter !== 'Semua bidang', fn ($q) => $q->whereJsonContains('practice_areas', $filter))
                ->when($request->query('q'), fn ($q, $keyword) => $q->where(function ($q) use ($keyword) {
                    $q->where('title', 'like', "%{$keyword}%")
                        ->orWhereHas('lawFirm', fn ($q) => $q->where('name', 'like', "%{$keyword}%"));
                }))
                ->get();

        $internshipApplicationFirmIds = $ca->internshipApplications()->with('jobPosting')->get()->pluck('jobPosting.law_firm_id')->filter()->all();

        return view('candidate.lowongan', [
            'jobPostings' => $jobPostings,
            'bidangFilter' => $bidangFilter,
            'filter' => $filter,
            'internshipApplicationFirmIds' => $internshipApplicationFirmIds,
            'keyword' => $request->query('q'),
            'canApplyForInternship' => $ca->canApplyForInternship(),
            'awaitingMatch' => $awaitingMatch,
        ]);
    }

    public function lamar(JobPosting $jobPosting): RedirectResponse
    {
        $ca = Auth::user()->candidateAdvocate;

        abort_unless($ca->isMatchedToFirm($jobPosting->law_firm_id), 403);

        if (! $ca->canApplyForInternship()) {
            throw ValidationException::withMessages([
                'lamaran' => 'Verifikasi admisi belum disetujui Admin DPC. Selesaikan checklist di Verifikasi Admisi terlebih dahulu.',
            ]);
        }

        if (! $ca->internshipApplications()->where('job_posting_id', $jobPosting->id)->exists()) {
            $ca->internshipApplications()->create([
                'job_posting_id' => $jobPosting->id,
                'status' => InternshipApplication::STATUS_SUBMITTED,
                'applied_on' => now(),
            ]);
        }

        return back()->with('status', 'Lamaran berhasil dikirim ke '.$jobPosting->lawFirm->name.'.');
    }
}
