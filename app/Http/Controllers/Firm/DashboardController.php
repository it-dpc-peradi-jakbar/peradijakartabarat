<?php

namespace App\Http\Controllers\Firm;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\InternshipApplication;
use App\Models\LogbookEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $pendamping = Auth::user()->supervisingLawyer;
        $firm = $pendamping->lawFirm;

        $kuotaTerpakai = $firm->kuotaTerpakai();
        $kuotaSlots = collect(range(1, $firm->max_quota))->map(fn ($i) => $i <= $kuotaTerpakai);

        $pelamarMenunggu = InternshipApplication::whereHas('jobPosting', fn ($q) => $q->where('law_firm_id', $firm->id))
            ->whereIn('status', ['SUBMITTED', 'CV_REVIEW', 'INTERVIEW'])
            ->count();

        $logbookBelumTtd = 0;
        if (config('features.logbook')) {
            $logbookBelumTtd = LogbookEntry::whereIn('candidate_advocate_id', $firm->candidateAdvocates()->pluck('id'))
                ->where('status', 'PENDING_SIGNATURE')
                ->count();
        }

        $mendekatiSelesai = $firm->candidateAdvocates()
            ->where('membership_status', 'ACTIVE')
            ->get()
            ->filter(fn ($ca) => $ca->bulanBerjalan() >= $ca->internship_months - 2)
            ->count();

        $firmTasks = [];
        if ($pelamarMenunggu > 0) {
            $firmTasks[] = [
                'label' => $pelamarMenunggu.' pelamar menunggu review CV',
                'sub' => 'Kuota tersisa '.$firm->kuotaTersisa().' slot',
                'cta' => 'Buka pelamar',
                'route' => route('firm.pelamar'),
            ];
        }
        if ($logbookBelumTtd > 0) {
            $firmTasks[] = [
                'label' => $logbookBelumTtd.' entri logbook belum ditandatangani',
                'sub' => 'Rekap bulan ini',
                'cta' => 'Review logbook',
                'route' => route('firm.logbook'),
            ];
        }
        if ($mendekatiSelesai > 0) {
            $firmTasks[] = [
                'label' => 'Pemagang mendekati masa selesai',
                'sub' => $mendekatiSelesai.' pemagang mendekati bulan ke-'.($firm->candidateAdvocates()->first()?->internship_months ?? 24),
                'cta' => 'Lihat pemagang',
                'route' => route('firm.dashboard'),
            ];
        }

        $pemagang = $firm->candidateAdvocates()->with('user')->get()->map(function ($ca) {
            $row = [
                'ca' => $ca,
                'logStatus' => null,
                'variant' => 'mute',
            ];
            if (! config('features.logbook')) {
                return $row;
            }

            $lastEntry = $ca->logbookEntries()->latest('entry_date')->first();
            if (! $lastEntry) {
                $row['logStatus'] = 'Belum ada entri';
            } elseif ($lastEntry->entry_date->diffInDays(now()) > 7) {
                $row['logStatus'] = 'Terlambat '.$lastEntry->entry_date->diffInDays(now()).' hari';
                $row['variant'] = 'bad';
            } elseif ($ca->logbookEntries()->where('status', 'PENDING_SIGNATURE')->exists()) {
                $row['logStatus'] = 'Menunggu ttd';
                $row['variant'] = 'wait';
            } else {
                $row['logStatus'] = 'Lengkap';
                $row['variant'] = 'ok';
            }

            return $row;
        });

        return view('firm.dashboard', [
            'firm' => $firm,
            'kuotaTerpakai' => $kuotaTerpakai,
            'kuotaSlots' => $kuotaSlots,
            'firmTasks' => $firmTasks,
            'pemagang' => $pemagang,
            'banners' => Banner::published()->forTarget(Banner::TARGET_FIRM)->orderByDesc('published_at')->get(),
        ]);
    }
}
