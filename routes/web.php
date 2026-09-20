<?php

use App\Http\Controllers\Admin\BannerController as AdminBannerController;
use App\Http\Controllers\Admin\MatchmakingController as AdminMatchmakingController;
use App\Http\Controllers\Admin\MonitoringController as AdminMonitoringController;
use App\Http\Controllers\Admin\RegistrantsController as AdminRegistrantsController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\VerifikasiController as AdminVerifikasiController;
use App\Http\Controllers\WilayahController;
use App\Http\Controllers\Candidate\BerkasController;
use App\Http\Controllers\Candidate\DashboardController as CandidateDashboardController;
use App\Http\Controllers\Candidate\LamaranController;
use App\Http\Controllers\Candidate\LogbookController;
use App\Http\Controllers\Candidate\LowonganController;
use App\Http\Controllers\Candidate\PreferenceController;
use App\Http\Controllers\Candidate\ReportController as CandidateReportController;
use App\Http\Controllers\Candidate\VerificationController as CandidateVerificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Firm\DashboardController as FirmDashboardController;
use App\Http\Controllers\Firm\LogbookController as FirmLogbookController;
use App\Http\Controllers\Firm\LowonganController as FirmLowonganController;
use App\Http\Controllers\Firm\PelamarController;
use App\Http\Controllers\Firm\ReportController as FirmReportController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/up', function () {
    return response()->noContent();
});

Route::get('/', HomeController::class);

Route::get('/wilayah', [WilayahController::class, 'index'])->name('wilayah.index');
Route::get('/wilayah/provinsi', [WilayahController::class, 'index'])->name('wilayah.provinsi');

Route::get('/dashboard', DashboardController::class)->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:calon_advokat'])->prefix('candidate')->name('candidate.')->group(function () {
    Route::get('/dashboard', [CandidateDashboardController::class, 'index'])->name('dashboard');
    Route::get('/lowongan', [LowonganController::class, 'index'])->name('lowongan');
    Route::post('/lowongan/{jobPosting}/lamar', [LowonganController::class, 'lamar'])->name('lowongan.lamar');
    Route::get('/lamaran', [LamaranController::class, 'index'])->name('lamaran');
    Route::get('/report', [CandidateReportController::class, 'create'])->name('report.create');
    Route::post('/report', [CandidateReportController::class, 'store'])->name('report.store');
    Route::get('/logbook', [LogbookController::class, 'index'])->name('logbook');
    Route::post('/logbook', [LogbookController::class, 'store'])->name('logbook.store');
    Route::get('/berkas', [BerkasController::class, 'index'])->name('berkas');
    Route::get('/verification', [CandidateVerificationController::class, 'index'])->name('verification');
    Route::post('/verification/{verificationChecklist}/link', [CandidateVerificationController::class, 'storeLink'])->name('verification.link');
    Route::get('/preferences', [PreferenceController::class, 'edit'])->name('preferences.edit');
    Route::patch('/preferences', [PreferenceController::class, 'update'])->name('preferences.update');
});

Route::middleware('auth')->group(function () {
    Route::redirect('/calon', '/candidate/dashboard');
    Route::redirect('/calon/dashboard', '/candidate/dashboard');
    Route::redirect('/calon/lowongan', '/candidate/lowongan');
    Route::redirect('/calon/lamaran', '/candidate/lamaran');
    Route::redirect('/calon/logbook', '/candidate/logbook');
    Route::redirect('/calon/berkas', '/candidate/berkas');
    Route::redirect('/calon/verifikasi', '/candidate/verification');
});

Route::middleware(['auth', 'role:law_firm'])->prefix('firm')->name('firm.')->group(function () {
    Route::get('/dashboard', [FirmDashboardController::class, 'index'])->name('dashboard');
    Route::get('/lowongan', [FirmLowonganController::class, 'index'])->name('lowongan');
    Route::get('/lowongan/{jobPosting}/edit', [FirmLowonganController::class, 'edit'])->name('lowongan.edit')->whereNumber('jobPosting');
    Route::patch('/lowongan/{jobPosting}', [FirmLowonganController::class, 'update'])->name('lowongan.update')->whereNumber('jobPosting');
    Route::get('/pelamar', [PelamarController::class, 'index'])->name('pelamar');
    Route::post('/pelamar/{internshipApplication}/terima', [PelamarController::class, 'terima'])->name('pelamar.terima');
    Route::get('/report', [FirmReportController::class, 'create'])->name('report.create');
    Route::post('/report', [FirmReportController::class, 'store'])->name('report.store');
    Route::get('/logbook', [FirmLogbookController::class, 'index'])->name('logbook');
    Route::post('/logbook/{entry}/setujui', [FirmLogbookController::class, 'setujui'])->name('logbook.setujui');
    Route::post('/logbook/{entry}/revisi', [FirmLogbookController::class, 'revisi'])->name('logbook.revisi');
    Route::post('/logbook/rekap/{monthlyLogbookSummary}/tandatangani-semua', [FirmLogbookController::class, 'tandatanganiSemua'])->name('logbook.tandatangani-semua');
});

Route::middleware(['auth', 'role:admin_dpc'])->prefix('admin')->name('admin.')->group(function () {
    Route::redirect('/verifikasi', '/admin/verification');
    Route::get('/verification', [AdminVerifikasiController::class, 'index'])->name('verification');
    Route::get('/verification/candidate/{candidateAdvocate}', [AdminVerifikasiController::class, 'showCalon'])->name('verification.candidate.show');
    Route::post('/verification/candidate/{candidateAdvocate}/setujui', [AdminVerifikasiController::class, 'setujuiCalon'])->name('verification.candidate.setujui');
    Route::post('/verification/checklist/{verificationChecklist}/setujui', [AdminVerifikasiController::class, 'setujuiChecklistItem'])->name('verification.checklist.setujui');
    Route::post('/verification/checklist/{verificationChecklist}/tolak', [AdminVerifikasiController::class, 'tolakChecklistItem'])->name('verification.checklist.tolak');
    Route::post('/verification/firm/{lawFirm}/tetapkan-kuota', [AdminVerifikasiController::class, 'tetapkanKuotaFirm'])->name('verification.firm.tetapkan-kuota');
    Route::get('/monitoring', [AdminMonitoringController::class, 'index'])->name('monitoring');
    Route::get('/registrants', [AdminRegistrantsController::class, 'index'])->name('registrants');
    Route::get('/matchmaking', [AdminMatchmakingController::class, 'index'])->name('matchmaking');
    Route::get('/matchmaking/{candidateAdvocate}', [AdminMatchmakingController::class, 'edit'])->name('matchmaking.edit');
    Route::put('/matchmaking/{candidateAdvocate}', [AdminMatchmakingController::class, 'update'])->name('matchmaking.update');
    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{report}', [AdminReportController::class, 'show'])->name('reports.show');
    Route::resource('banners', AdminBannerController::class)->except(['show']);
});

require __DIR__.'/auth.php';
