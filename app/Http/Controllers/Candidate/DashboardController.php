<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $ca = Auth::user()->candidateAdvocate()->with(['lawFirm', 'supervisingLawyer'])->firstOrFail();

        $hasLamaran = $ca->internshipApplications()->exists();
        $ditempatkan = $ca->law_firm_id !== null;
        $magangSelesai = $ditempatkan && $ca->bulanBerjalan() >= $ca->internship_months;
        $audit = $ca->finalAudits()->latest()->first();
        $lulusAudit = $audit?->status === 'PASSED';

        $timeline = [
            ['n' => 1, 'label' => 'Registrasi & unggah sertifikat UPA', 'actor' => 'Calon Advokat', 'status' => 'Selesai'],
            ['n' => 2, 'label' => 'Validasi kelulusan & keanggotaan', 'actor' => 'Admin DPC', 'status' => $ca->verification_status === 'VERIFIED' ? 'Selesai' : 'Berjalan'],
            ['n' => 6, 'label' => 'Pengajuan lamaran magang', 'actor' => 'Calon Advokat', 'status' => $hasLamaran ? 'Selesai' : ($ca->verification_status === 'VERIFIED' ? 'Berjalan' : 'Terkunci')],
            ['n' => 8, 'label' => 'Penerbitan surat penerimaan', 'actor' => 'Law Firm', 'status' => $ditempatkan ? 'Selesai' : ($hasLamaran ? 'Berjalan' : 'Terkunci')],
            ['n' => 9, 'label' => 'Input logbook harian', 'actor' => 'Calon Advokat', 'status' => $magangSelesai ? 'Selesai' : ($ditempatkan ? 'Berjalan' : 'Terkunci')],
            ['n' => 10, 'label' => 'Review & tanda tangan logbook', 'actor' => 'Pendamping', 'status' => $magangSelesai ? 'Selesai' : ($ditempatkan ? 'Berjalan' : 'Terkunci')],
            ['n' => 13, 'label' => 'Audit akhir kelayakan sumpah', 'actor' => 'Admin DPC', 'status' => $lulusAudit ? 'Selesai' : ($magangSelesai ? 'Berjalan' : 'Menunggu')],
            ['n' => 14, 'label' => 'Unduh paket berkas sumpah', 'actor' => 'Calon Advokat', 'status' => $lulusAudit ? 'Selesai' : 'Terkunci'],
        ];

        $totalLog = $ca->logbookEntries()->count();
        $disetujui = $ca->logbookEntries()->where('status', 'APPROVED')->count();
        $kepatuhan = $totalLog > 0 ? (int) round(($disetujui / $totalLog) * 100) : 0;

        $stats = config('features.logbook')
            ? [
                ['value' => $totalLog, 'label' => 'Entri logbook tercatat'],
                ['value' => $kepatuhan.'%', 'label' => 'Kepatuhan pengisian'],
                ['value' => $ca->monthlyLogbookSummaries()->where('status', 'SIGNED')->count(), 'label' => 'Rekap bulanan ditandatangani'],
                ['value' => $ca->monthlyLogbookSummaries()->where('status', 'PENDING_SIGNATURE')->count(), 'label' => 'Menunggu tanda tangan'],
            ]
            : [
                ['value' => $ca->internshipApplications()->count(), 'label' => 'Lamaran terkirim'],
                ['value' => $ca->verification_status === 'VERIFIED' ? 'Ya' : 'Belum', 'label' => 'Verifikasi admisi'],
                ['value' => $ca->bulanBerjalan().'/'.$ca->internship_months, 'label' => 'Bulan magang'],
                ['value' => $ca->lawFirm->name ?? '—', 'label' => 'Penempatan'],
            ];

        return view('candidate.dashboard', [
            'ca' => $ca,
            'timeline' => $timeline,
            'stats' => $stats,
            'banners' => Banner::published()->forTarget(Banner::TARGET_CALON)->orderByDesc('published_at')->get(),
        ]);
    }
}
