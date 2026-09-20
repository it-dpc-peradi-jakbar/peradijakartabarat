<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\MonthlyLogbookSummary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LogbookController extends Controller
{
    public function index(): View
    {
        abort_unless(config('features.logbook'), 404);

        $ca = Auth::user()->candidateAdvocate;

        $now = now();
        $entries = $ca->logbookEntries()
            ->whereYear('entry_date', $now->year)
            ->whereMonth('entry_date', $now->month)
            ->orderByDesc('entry_date')
            ->get();

        return view('candidate.logbook', [
            'ca' => $ca,
            'entries' => $entries,
            'bulanLabel' => $now->translatedFormat('F Y'),
            'pending' => $entries->where('status', 'PENDING_SIGNATURE')->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(config('features.logbook'), 404);

        $data = $request->validate([
            'activity_type' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'hours' => ['required', 'numeric', 'min:0.5', 'max:24'],
        ]);

        $ca = Auth::user()->candidateAdvocate;

        $ca->logbookEntries()->create([
            'entry_date' => now(),
            'activity_type' => $data['activity_type'],
            'hours' => $data['hours'],
            'description' => $data['description'],
            'status' => 'PENDING_SIGNATURE',
        ]);

        $now = now();
        MonthlyLogbookSummary::firstOrCreate(
            ['candidate_advocate_id' => $ca->id, 'month' => $now->month, 'year' => $now->year],
            ['status' => 'IN_PROGRESS']
        );

        return back()->with('status', 'Catatan harian tersimpan dan menunggu tanda tangan pendamping.');
    }
}
