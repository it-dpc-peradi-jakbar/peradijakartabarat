<?php

namespace App\Http\Controllers\Firm;

use App\Http\Controllers\Controller;
use App\Models\InternshipApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PelamarController extends Controller
{
    public function index(): View
    {
        $pendamping = Auth::user()->supervisingLawyer;
        $firm = $pendamping->lawFirm;

        $internshipApplications = InternshipApplication::whereHas('jobPosting', fn ($q) => $q->where('law_firm_id', $firm->id))
            ->whereHas('candidateAdvocate.matchedLawFirms', fn ($q) => $q->where('law_firms.id', $firm->id))
            ->where('status', '!=', InternshipApplication::STATUS_REJECTED)
            ->with(['candidateAdvocate.user', 'jobPosting'])
            ->orderByDesc('applied_on')
            ->get();

        return view('firm.pelamar', ['internshipApplications' => $internshipApplications, 'firm' => $firm]);
    }

    public function terima(InternshipApplication $internshipApplication): RedirectResponse
    {
        $pendamping = Auth::user()->supervisingLawyer;
        abort_unless($internshipApplication->jobPosting->law_firm_id === $pendamping->law_firm_id, 403);

        $internshipApplication->update(['status' => InternshipApplication::STATUS_ACCEPTED]);

        $ca = $internshipApplication->candidateAdvocate;
        $ca->update([
            'law_firm_id' => $pendamping->law_firm_id,
            'supervising_lawyer_id' => $ca->supervising_lawyer_id ?? $pendamping->id,
            'internship_started_on' => $ca->internship_started_on ?? now(),
            'placement_area' => $ca->placement_area ?? $internshipApplication->jobPosting->title,
        ]);

        return back()->with('status', 'Surat penerimaan magang diterbitkan untuk '.$ca->user->name.'.');
    }
}
