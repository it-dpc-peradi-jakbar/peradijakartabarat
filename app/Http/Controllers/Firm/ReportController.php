<?php

namespace App\Http\Controllers\Firm;

use App\Http\Controllers\Controller;
use App\Models\CandidateAdvocate;
use App\Models\InternshipApplication;
use App\Models\Report;
use App\Support\HtmlSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function create(): View
    {
        $firm = Auth::user()->supervisingLawyer->lawFirm;
        $candidates = CandidateAdvocate::query()
            ->whereHas('matchedLawFirms', fn ($q) => $q->where('law_firms.id', $firm->id))
            ->whereHas('internshipApplications', function ($q) use ($firm) {
                $q->where('status', '!=', InternshipApplication::STATUS_REJECTED)
                    ->whereHas('jobPosting', fn ($q) => $q->where('law_firm_id', $firm->id));
            })
            ->with('user')
            ->orderBy('candidate_code')
            ->get();

        return view('firm.report', [
            'firm' => $firm,
            'candidates' => $candidates,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $firm = Auth::user()->supervisingLawyer->lawFirm;
        $data = $request->validate([
            'candidate_advocate_id' => ['required', 'integer', 'exists:candidate_advocates,id'],
            'body' => ['required', 'string'],
        ]);

        $ca = CandidateAdvocate::findOrFail($data['candidate_advocate_id']);
        abort_unless($ca->isMatchedToFirm($firm->id), 403);
        abort_unless(
            InternshipApplication::where('candidate_advocate_id', $ca->id)
                ->whereHas('jobPosting', fn ($q) => $q->where('law_firm_id', $firm->id))
                ->exists(),
            403
        );

        $body = HtmlSanitizer::reportBody($data['body']);
        if ($body === '') {
            throw ValidationException::withMessages([
                'body' => 'Isi laporan tidak boleh kosong.',
            ]);
        }

        Report::create([
            'submitter_user_id' => $request->user()->id,
            'candidate_advocate_id' => $ca->id,
            'law_firm_id' => $firm->id,
            'body' => $body,
        ]);

        return redirect()->route('firm.report.create')->with('status', 'Laporan terkirim ke Admin DPC.');
    }
}
