<?php

namespace App\Http\Controllers\Firm;

use App\Http\Controllers\Controller;
use App\Models\CandidateAdvocate;
use App\Models\LogbookEntry;
use App\Models\MonthlyLogbookSummary;
use App\Models\SupervisingLawyer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LogbookController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(config('features.logbook'), 404);

        $pendamping = Auth::user()->supervisingLawyer;
        $calonList = $pendamping->lawFirm->candidateAdvocates()->with('user')->get();

        $selectedId = $request->query('calon');
        $selected = $selectedId
            ? $calonList->firstWhere('id', (int) $selectedId)
            : $calonList->first(fn ($ca) => $ca->logbookEntries()->where('status', 'PENDING_SIGNATURE')->exists());
        $selected ??= $calonList->first();

        $entries = collect();
        $monthlyLogbookSummary = null;
        if ($selected) {
            $now = now();
            $entries = $selected->logbookEntries()
                ->whereYear('entry_date', $now->year)
                ->whereMonth('entry_date', $now->month)
                ->orderByDesc('entry_date')
                ->get();

            $monthlyLogbookSummary = MonthlyLogbookSummary::firstOrCreate(
                ['candidate_advocate_id' => $selected->id, 'month' => $now->month, 'year' => $now->year],
                ['status' => 'IN_PROGRESS']
            );
        }

        return view('firm.logbook', [
            'pendamping' => $pendamping,
            'calonList' => $calonList,
            'selected' => $selected,
            'entries' => $entries,
            'monthlyLogbookSummary' => $monthlyLogbookSummary,
            'bulanLabel' => now()->translatedFormat('F Y'),
        ]);
    }

    public function setujui(LogbookEntry $entry): RedirectResponse
    {
        abort_unless(config('features.logbook'), 404);
        $this->authorizeEntry($entry);
        $entry->update(['status' => 'APPROVED', 'revision_notes' => null]);

        return back()->with('status', 'Entri logbook disetujui.');
    }

    public function revisi(Request $request, LogbookEntry $entry): RedirectResponse
    {
        abort_unless(config('features.logbook'), 404);
        $this->authorizeEntry($entry);
        $data = $request->validate(['revision_notes' => ['nullable', 'string', 'max:500']]);
        $entry->update(['status' => 'REVISION', 'revision_notes' => $data['revision_notes'] ?? 'Perlu direvisi oleh calon advokat.']);

        return back()->with('status', 'Entri logbook dikembalikan untuk revisi.');
    }

    public function tandatanganiSemua(MonthlyLogbookSummary $monthlyLogbookSummary): RedirectResponse
    {
        abort_unless(config('features.logbook'), 404);
        $pendamping = Auth::user()->supervisingLawyer;
        $this->authorizeCandidate($monthlyLogbookSummary->candidateAdvocate, $pendamping);

        $monthlyLogbookSummary->candidateAdvocate->logbookEntries()
            ->whereYear('entry_date', $monthlyLogbookSummary->year)
            ->whereMonth('entry_date', $monthlyLogbookSummary->month)
            ->whereIn('status', ['PENDING_SIGNATURE', 'REVISION'])
            ->update(['status' => 'APPROVED', 'revision_notes' => null]);

        $monthlyLogbookSummary->update([
            'status' => 'SIGNED',
            'signed_by_supervising_lawyer_id' => $pendamping->id,
            'signed_at' => now(),
        ]);

        return back()->with('status', 'Seluruh entri disetujui dan rekap bulanan ditandatangani.');
    }

    private function authorizeEntry(LogbookEntry $entry): void
    {
        $pendamping = Auth::user()->supervisingLawyer;
        $this->authorizeCandidate($entry->candidateAdvocate, $pendamping);
    }

    private function authorizeCandidate(?CandidateAdvocate $candidate, ?SupervisingLawyer $pendamping): void
    {
        abort_if($pendamping === null || $candidate === null, 403);

        abort_unless(
            (int) $candidate->law_firm_id === (int) $pendamping->law_firm_id,
            403
        );
    }
}
