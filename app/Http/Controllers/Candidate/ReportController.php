<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\LawFirm;
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
        $ca = Auth::user()->candidateAdvocate;
        $firms = LawFirm::query()
            ->where('verification_status', 'VERIFIED')
            ->whereIn('id', $ca->matchedJobPostings()->pluck('job_postings.law_firm_id'))
            ->orderBy('name')
            ->get();

        return view('candidate.report', [
            'ca' => $ca,
            'firms' => $firms,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $ca = Auth::user()->candidateAdvocate;
        $data = $request->validate([
            'law_firm_id' => ['required', 'integer', 'exists:law_firms,id'],
            'body' => ['required', 'string'],
        ]);

        $firm = LawFirm::findOrFail($data['law_firm_id']);
        abort_unless(
            $ca->isMatchedToFirm($firm->id) && $firm->verification_status === 'VERIFIED',
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

        return redirect()->route('candidate.report.create')->with('status', 'Laporan terkirim ke Admin DPC.');
    }
}
