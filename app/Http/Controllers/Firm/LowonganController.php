<?php

namespace App\Http\Controllers\Firm;

use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LowonganController extends Controller
{
    public function index(): View
    {
        $firm = Auth::user()->supervisingLawyer->lawFirm;
        $jobPostings = $firm->jobPostings()->orderBy('title')->get();

        return view('firm.lowongan', [
            'firm' => $firm,
            'jobPostings' => $jobPostings,
        ]);
    }

    public function update(Request $request, JobPosting $jobPosting): RedirectResponse
    {
        $firmId = Auth::user()->supervisingLawyer->law_firm_id;
        abort_unless($jobPosting->law_firm_id === $firmId, 403);

        $jobPosting->update([
            'provides_transport' => $request->boolean('provides_transport'),
        ]);

        return back()->with('status', 'Preferensi lowongan '.$jobPosting->title.' disimpan.');
    }
}
