<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\CandidateAdvocate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PreferenceController extends Controller
{
    public function edit(): View
    {
        $ca = Auth::user()->candidateAdvocate;

        return view('candidate.preferences', ['ca' => $ca]);
    }

    public function update(Request $request): RedirectResponse
    {
        $ca = Auth::user()->candidateAdvocate;
        $ca->update([
            'work_reference' => $request->boolean('work_reference_global')
                ? [CandidateAdvocate::WORK_REFERENCE_GLOBAL]
                : [],
            'wants_transport' => $request->boolean('wants_transport'),
        ]);

        return redirect()->route('candidate.preferences.edit')->with('status', 'Preferensi magang disimpan.');
    }
}
