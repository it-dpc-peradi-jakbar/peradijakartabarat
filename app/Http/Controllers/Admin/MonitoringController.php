<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CandidateAdvocate;
use App\Models\LawFirm;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    public function index(): View
    {
        $kepatuhan = collect();
        if (config('features.logbook')) {
            $kepatuhan = LawFirm::where('verification_status', 'VERIFIED')
                ->get()
                ->map(fn ($firm) => [
                    'firm' => $firm,
                    'pct' => $firm->kepatuhanLogbookPersen(),
                ])
                ->sortByDesc('pct')
                ->values();
        }

        $alerts = [];

        $firmPenuh = LawFirm::where('verification_status', 'VERIFIED')->get()
            ->first(fn ($f) => $f->max_quota > 0 && $f->kuotaTerpakai() / $f->max_quota >= 0.9);
        if ($firmPenuh) {
            $alerts[] = [
                'title' => 'Kuota gabungan hampir penuh',
                'detail' => $firmPenuh->name.': '.$firmPenuh->kuotaTerpakai().' dari '.$firmPenuh->max_quota.' slot terpakai.',
                'variant' => 'warn',
            ];
        }

        if (config('features.logbook')) {
            $telat = CandidateAdvocate::where('membership_status', 'ACTIVE')->whereNotNull('law_firm_id')->with(['user', 'lawFirm'])->get()
                ->map(function ($ca) {
                    $last = $ca->logbookEntries()->latest('entry_date')->first();
                    $days = $last ? $last->entry_date->diffInDays(now()) : ($ca->internship_started_on?->diffInDays(now()) ?? 0);

                    return ['ca' => $ca, 'days' => $days];
                })
                ->sortByDesc('days')
                ->first(fn ($row) => $row['days'] >= 14);
            if ($telat) {
                $alerts[] = [
                    'title' => 'Logbook tidak diisi '.$telat['days'].' hari',
                    'detail' => $telat['ca']->user->name.' — '.($telat['ca']->lawFirm->name ?? '-').'.',
                    'variant' => 'bad',
                ];
            }
        }

        $mendekati = CandidateAdvocate::where('membership_status', 'ACTIVE')->whereNotNull('law_firm_id')->with(['user', 'lawFirm'])->get()
            ->first(fn ($ca) => $ca->internship_months - $ca->bulanBerjalan() <= 2 && $ca->internship_months - $ca->bulanBerjalan() > 0);
        if ($mendekati) {
            $sisa = $mendekati->internship_months - $mendekati->bulanBerjalan();
            $alerts[] = [
                'title' => 'Masa magang mendekati '.$mendekati->internship_months.' bulan',
                'detail' => $mendekati->user->name.' — sisa '.$sisa.' bulan, berkas mulai disiapkan.',
                'variant' => 'info',
            ];
        }

        $auditList = CandidateAdvocate::whereNotNull('law_firm_id')
            ->with(['user', 'finalAudits' => fn ($q) => $q->latest()])
            ->get()
            ->filter(fn ($ca) => $ca->bulanBerjalan() >= $ca->internship_months - 2)
            ->map(function ($ca) {
                $audit = $ca->finalAudits->first();
                $status = $audit?->status ?? 'IN_PROGRESS';
                $detail = $ca->bulanBerjalan().'/'.$ca->internship_months.' bulan';
                if (config('features.logbook')) {
                    $monthlyLogbookSummaryBelumTtd = $ca->monthlyLogbookSummaries()->where('status', '!=', 'SIGNED')->count();
                    $detail .= $monthlyLogbookSummaryBelumTtd > 0 ? ' · '.$monthlyLogbookSummaryBelumTtd.' rekap belum ditandatangani' : ' · logbook lengkap';
                }

                return ['ca' => $ca, 'status' => $status, 'detail' => $detail];
            })
            ->values();

        return view('admin.monitoring', [
            'kepatuhan' => $kepatuhan,
            'alerts' => $alerts,
            'auditList' => $auditList,
        ]);
    }
}
