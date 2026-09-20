<?php

namespace App\Support;

use App\Models\CandidateAdvocate;
use App\Models\InternshipApplication;
use App\Models\LawFirm;
use App\Models\Report;
use App\Support\CandidateVerificationChecklist;
use Illuminate\Support\Facades\Auth;

class SidebarMenu
{
    public static function candidate(string $active): array
    {
        $ca = Auth::user()->candidateAdvocate;
        $verificationBadge = $ca && in_array($ca->verification_status, ['PENDING', 'NEEDS_CORRECTION'], true)
            ? CandidateVerificationChecklist::pendingUploadCount($ca)
            : 0;

        $items = [
            ['label' => 'Dashboard', 'route' => route('candidate.dashboard'), 'active' => $active === 'dashboard'],
            ['label' => 'Verifikasi Admisi', 'route' => route('candidate.verification'), 'active' => $active === 'verification', 'badge' => $verificationBadge ?: null],
            ['label' => 'Preferensi magang', 'route' => route('candidate.preferences.edit'), 'active' => $active === 'preferences'],
            ['label' => 'Cari Lowongan', 'route' => route('candidate.lowongan'), 'active' => $active === 'lowongan'],
            ['label' => 'Lamaran Saya', 'route' => route('candidate.lamaran'), 'active' => $active === 'lamaran'],
            ['label' => 'Laporan', 'route' => route('candidate.report.create'), 'active' => $active === 'report'],
        ];

        if (config('features.logbook')) {
            $logbookBadge = $ca ? $ca->logbookEntries()->where('status', 'PENDING_SIGNATURE')->count() : 0;
            array_splice($items, 4, 0, [[
                'label' => 'Logbook Digital',
                'route' => route('candidate.logbook'),
                'active' => $active === 'logbook',
                'badge' => $logbookBadge ?: null,
            ]]);
        }

        $items[] = ['label' => 'Berkas Sumpah', 'route' => route('candidate.berkas'), 'active' => $active === 'berkas'];

        return $items;
    }

    public static function firm(string $active): array
    {
        $pendamping = Auth::user()->supervisingLawyer;
        $pelamarBadge = 0;
        if ($pendamping) {
            $firmId = $pendamping->law_firm_id;
            $pelamarBadge = InternshipApplication::whereHas('jobPosting', fn ($q) => $q->where('law_firm_id', $firmId))
                ->matchedToJob()
                ->whereIn('status', ['SUBMITTED', 'CV_REVIEW', 'INTERVIEW'])
                ->count();
        }

        $items = [
            ['label' => 'Dashboard & Kuota', 'route' => route('firm.dashboard'), 'active' => $active === 'dashboard'],
            ['label' => 'Lowongan', 'route' => route('firm.lowongan'), 'active' => $active === 'lowongan'],
            ['label' => 'Pelamar', 'route' => route('firm.pelamar'), 'active' => $active === 'pelamar', 'badge' => $pelamarBadge ?: null],
            ['label' => 'Laporan', 'route' => route('firm.report.create'), 'active' => $active === 'report'],
        ];

        if (config('features.logbook')) {
            $items[] = ['label' => 'Review Logbook', 'route' => route('firm.logbook'), 'active' => $active === 'logbook'];
        }

        return $items;
    }

    public static function admin(string $active): array
    {
        $badge = CandidateAdvocate::whereIn('verification_status', ['PENDING', 'NEEDS_CORRECTION'])->count()
            + LawFirm::whereIn('verification_status', ['PENDING', 'NEEDS_CORRECTION'])->count();
        $reportBadge = Report::whereNull('read_at')->count();

        return [
            ['label' => 'Verifikasi', 'route' => route('admin.verification'), 'active' => $active === 'verification', 'badge' => $badge ?: null],
            ['label' => 'Pencocokan', 'route' => route('admin.matchmaking'), 'active' => $active === 'matchmaking'],
            ['label' => 'Laporan', 'route' => route('admin.reports.index'), 'active' => $active === 'reports', 'badge' => $reportBadge ?: null],
            ['label' => 'Pengumuman', 'route' => route('admin.banners.index'), 'active' => $active === 'banners'],
            ['label' => 'Data Terdaftar', 'route' => route('admin.registrants'), 'active' => $active === 'registrants'],
            ['label' => 'Monitoring & Audit', 'route' => route('admin.monitoring'), 'active' => $active === 'monitoring'],
        ];
    }
}
