<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\SupervisingLawyer;
use App\Models\FinalAudit;
use App\Models\OathDocument;
use App\Models\CandidateAdvocate;
use App\Models\InternshipApplication;
use App\Models\LawFirm;
use App\Models\LogbookEntry;
use App\Models\MonthlyLogbookSummary;
use App\Models\JobPosting;
use App\Models\User;
use App\Models\VerificationChecklist;
use App\Support\CandidateVerificationChecklist;
use App\Support\WilayahHierarchy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(WilayahSeeder::class);

        $password = Hash::make(DemoUserCatalog::PASSWORD);
        $jakbar = WilayahHierarchy::jakbar();

        // ---- Law firms (verified, provide lowongan, appear in monitoring) ----
        $wibisono = LawFirm::create($jakbar + [
            'name' => 'Wibisono & Rekan', 'address' => 'Jl. Panjang No. 12, Kebon Jeruk, Jakarta Barat',
            'ministry_registration_number' => 'AHU-0041223.AH.01.01', 'max_quota' => 10,
            'verification_status' => 'VERIFIED', 'verified_at' => now()->subMonths(20),
        ]);
        $santika = LawFirm::create($jakbar + [
            'name' => 'Santika, Hartono & Partners', 'address' => 'Jl. Kebon Jeruk Raya No. 8, Jakarta Barat',
            'ministry_registration_number' => 'AHU-0038812.AH.01.01', 'max_quota' => 10,
            'verification_status' => 'VERIFIED', 'verified_at' => now()->subMonths(18),
        ]);
        $ardiansyah = LawFirm::create($jakbar + [
            'name' => 'Kantor Hukum Ardiansyah', 'address' => 'Jl. Puri Kembangan No. 21, Jakarta Barat',
            'ministry_registration_number' => 'AHU-0029931.AH.01.01', 'max_quota' => 10,
            'verification_status' => 'VERIFIED', 'verified_at' => now()->subMonths(15),
        ]);
        $lbhTrisakti = LawFirm::create($jakbar + [
            'name' => 'LBH Kampus Universitas Trisakti', 'address' => 'Jl. Kyai Tapa No. 1, Grogol, Jakarta Barat',
            'ministry_registration_number' => null, 'is_equivalent_law_firm' => true, 'max_quota' => 10,
            'verification_status' => 'VERIFIED', 'verified_at' => now()->subMonths(24),
        ]);

        // ---- Law firms pending Admin DPC verification ----
        $wijaya = LawFirm::create($jakbar + [
            'name' => 'Wijaya Legal Consult', 'address' => 'Jl. Tanjung Duren Raya No. 45, Jakarta Barat',
            'ministry_registration_number' => 'AHU-0055102.AH.01.01', 'max_quota' => 10,
            'verification_status' => 'PENDING',
        ]);
        VerificationChecklist::insert([
            ['checkable_type' => LawFirm::class, 'checkable_id' => $wijaya->id, 'label' => 'Domisili kantor di wilayah DPC Jakbar', 'is_checked' => true, 'created_at' => now(), 'updated_at' => now()],
            ['checkable_type' => LawFirm::class, 'checkable_id' => $wijaya->id, 'label' => 'KTA pendamping aktif', 'is_checked' => true, 'created_at' => now(), 'updated_at' => now()],
            ['checkable_type' => LawFirm::class, 'checkable_id' => $wijaya->id, 'label' => 'Bukti pengalaman praktik dilampirkan', 'is_checked' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $kusuma = LawFirm::create($jakbar + [
            'name' => 'Kusuma & Associates', 'address' => 'Jl. Cengkareng Raya No. 9, Jakarta Barat',
            'ministry_registration_number' => 'AHU-0061187.AH.01.01', 'max_quota' => 8,
            'verification_status' => 'NEEDS_CORRECTION',
        ]);
        VerificationChecklist::insert([
            ['checkable_type' => LawFirm::class, 'checkable_id' => $kusuma->id, 'label' => 'Domisili kantor terverifikasi', 'is_checked' => true, 'created_at' => now(), 'updated_at' => now()],
            ['checkable_type' => LawFirm::class, 'checkable_id' => $kusuma->id, 'label' => 'KTA pendamping aktif', 'is_checked' => true, 'created_at' => now(), 'updated_at' => now()],
            ['checkable_type' => LawFirm::class, 'checkable_id' => $kusuma->id, 'label' => 'Bukti pengalaman praktik belum lengkap', 'is_checked' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ---- Advokat pendamping ----
        $userHendra = User::create([
            'name' => 'Dr. Hendra Wibisono', 'email' => 'lawfirm@peradijakbar.test',
            'password' => $password, 'role' => 'law_firm', 'email_verified_at' => now(),
        ]);
        $hendra = SupervisingLawyer::create([
            'law_firm_id' => $wibisono->id, 'user_id' => $userHendra->id, 'name' => 'Dr. Hendra Wibisono, S.H., M.H.',
            'bar_membership_number' => 'KTA-DPC-JB-00214', 'bar_membership_active' => true, 'years_of_experience' => 14,
        ]);
        $pendampingSantika = SupervisingLawyer::create([
            'law_firm_id' => $santika->id, 'name' => 'Ratna Santika, S.H., M.Kn.',
            'bar_membership_number' => 'KTA-DPC-JB-00187', 'bar_membership_active' => true, 'years_of_experience' => 11,
        ]);

        // ---- JobPosting (only from verified firms) ----
        JobPosting::create([
            'law_firm_id' => $santika->id, 'title' => 'Magang Calon Advokat — Litigasi Korporasi',
            'description' => 'Pendampingan perkara PKPU dan sengketa kontrak di PN Jakarta Barat.',
            'practice_areas' => ['Litigasi', 'Kepailitan', 'Full-time'], 'quota' => 10,
            'provides_transport' => true,
            'kabupaten_kota_kode' => $jakbar['kabupaten_kota_kode'],
        ]);
        JobPosting::create([
            'law_firm_id' => $ardiansyah->id, 'title' => 'Magang Calon Advokat — Hukum Keluarga',
            'description' => 'Riset dan penyusunan gugatan perceraian, waris, dan permohonan penetapan.',
            'practice_areas' => ['Perdata', 'Keluarga', 'Hybrid'], 'quota' => 10,
            'kabupaten_kota_kode' => $jakbar['kabupaten_kota_kode'],
        ]);
        JobPosting::create([
            'law_firm_id' => $lbhTrisakti->id, 'title' => 'Magang Calon Advokat — Bantuan Hukum',
            'description' => 'Pendampingan klien prodeo. Setara kantor advokat sesuai Peraturan PERADI No. 1/2015.',
            'practice_areas' => ['Pidana', 'Prodeo', 'Full-time'], 'quota' => 10,
            'kabupaten_kota_kode' => $jakbar['kabupaten_kota_kode'],
        ]);
        $jobPostingWibisono = JobPosting::create([
            'law_firm_id' => $wibisono->id, 'title' => 'Magang Calon Advokat — Litigasi Perdata & Kepailitan',
            'description' => 'Pendampingan perkara litigasi perdata dan kepailitan bersama advokat pendamping.',
            'practice_areas' => ['Litigasi', 'Korporasi', 'Full-time'], 'quota' => 10,
            'provides_transport' => true,
            'kabupaten_kota_kode' => $jakbar['kabupaten_kota_kode'],
        ]);

        // ---- Helper to create a calon advokat + login user ----
        $makeCalon = function (
            string $name, string $email, string $kode, array $attrs = []
        ) use ($password, $jakbar) {
            $user = User::create([
                'name' => $name, 'email' => $email, 'password' => $password,
                'role' => 'calon_advokat', 'email_verified_at' => now(),
            ]);

            return CandidateAdvocate::create(array_merge($jakbar, [
                'user_id' => $user->id,
                'candidate_code' => $kode,
                'membership_status' => 'ACTIVE',
                'verification_status' => 'VERIFIED',
            ], $attrs));
        };

        // Helper: generate a month of logbook entries with a given approval ratio.
        $seedLogbook = function (CandidateAdvocate $ca, int $months, float $approvalRatio, ?SupervisingLawyer $pendamping) {
            $jenis = ['Riset hukum', 'Pendampingan sidang', 'Drafting dokumen', 'Konsultasi internal'];
            for ($m = $months - 1; $m >= 0; $m--) {
                $monthDate = now()->subMonths($m);
                $entriesThisMonth = random_int(10, 16);
                $approvedInMonth = 0;
                for ($i = 0; $i < $entriesThisMonth; $i++) {
                    $approved = $m > 0 ? true : ($i / $entriesThisMonth) < $approvalRatio;
                    LogbookEntry::create([
                        'candidate_advocate_id' => $ca->id,
                        'entry_date' => $monthDate->copy()->startOfMonth()->addDays($i),
                        'activity_type' => $jenis[array_rand($jenis)],
                        'hours' => random_int(2, 7),
                        'description' => 'Catatan kegiatan magang harian di bidang '.strtolower($ca->placement_area ?? 'hukum').'.',
                        'status' => $approved ? 'APPROVED' : 'PENDING_SIGNATURE',
                    ]);
                    if ($approved) {
                        $approvedInMonth++;
                    }
                }
                MonthlyLogbookSummary::create([
                    'candidate_advocate_id' => $ca->id,
                    'month' => $monthDate->month,
                    'year' => $monthDate->year,
                    'signed_by_supervising_lawyer_id' => $approvedInMonth === $entriesThisMonth ? $pendamping?->id : null,
                    'status' => $approvedInMonth === $entriesThisMonth ? 'SIGNED' : ($m === 0 ? 'PENDING_SIGNATURE' : 'IN_PROGRESS'),
                    'signed_at' => $approvedInMonth === $entriesThisMonth ? $monthDate->copy()->endOfMonth() : null,
                ]);
            }
        };

        $berkasTemplate = fn (CandidateAdvocate $ca, array $overrides = []) => array_merge([
            ['document_type' => 'sertifikat_pkpa', 'source_label' => 'Admisi · terbit 14 Feb 2025', 'status' => 'COMPLETE', 'file_size_label' => '1,2 MB'],
            ['document_type' => 'sertifikat_lulus_upa', 'source_label' => 'Admisi · '.$ca->bar_exam_cohort, 'status' => 'COMPLETE', 'file_size_label' => '0,9 MB'],
            ['document_type' => 'ijazah_transkrip', 'source_label' => 'Admisi · auto-populate profil', 'status' => 'COMPLETE', 'file_size_label' => '3,4 MB'],
            ['document_type' => 'rekap_logbook', 'source_label' => 'Magang · '.$ca->bulanBerjalan().' bulan terekam', 'status' => 'IN_PROGRESS', 'file_size_label' => null],
            ['document_type' => 'sertifikat_selesai_magang', 'source_label' => 'Magang · diterbitkan kantor hukum', 'status' => 'PENDING', 'file_size_label' => null],
            ['document_type' => 'surat_rekomendasi_dpc', 'source_label' => 'Magang · setelah audit akhir', 'status' => 'PENDING', 'file_size_label' => null],
        ], $overrides);

        // ---- Andi Prasetyo (main demo login, 14/24 bulan) ----
        $andi = $makeCalon('Andi Prasetyo, S.H.', 'andi@peradijakbar.test', 'CA-2025-0417', [
            'national_id_number' => '3171012345670001', 'university' => 'Universitas Indonesia', 'gpa' => 3.55,
            'bar_exam_cohort' => 'Gelombang II 2025', 'bar_exam_graduation_year' => 2025,
            'law_firm_id' => $wibisono->id, 'supervising_lawyer_id' => $hendra->id,
            'placement_area' => 'Litigasi Perdata', 'internship_started_on' => now()->subMonths(14),
        ]);
        $seedLogbook($andi, 14, 0.5, $hendra);
        foreach ($berkasTemplate($andi) as $b) {
            OathDocument::create(array_merge(['candidate_advocate_id' => $andi->id], $b));
        }

        // ---- Rizky Alamsyah (22/24 bulan, lengkap, audit dalam proses) ----
        $rizky = $makeCalon('Rizky Alamsyah, S.H.', 'rizky@peradijakbar.test', 'CA-2024-0288', [
            'national_id_number' => '3171019876540002', 'university' => 'Universitas Trisakti', 'gpa' => 3.48,
            'bar_exam_cohort' => 'Gelombang I 2024', 'bar_exam_graduation_year' => 2024,
            'law_firm_id' => $wibisono->id, 'supervising_lawyer_id' => $hendra->id,
            'placement_area' => 'Kepailitan', 'internship_started_on' => now()->subMonths(22),
        ]);
        $seedLogbook($rizky, 22, 1.0, $hendra);
        FinalAudit::create(['candidate_advocate_id' => $rizky->id, 'status' => 'IN_PROGRESS', 'notes' => '1 rekap belum ditandatangani.']);

        // ---- Nadia Kusuma (11/24 bulan, lengkap) ----
        $nadia = $makeCalon('Nadia Kusuma, S.H.', 'nadia@peradijakbar.test', 'CA-2025-0512', [
            'national_id_number' => '3171015566770003', 'university' => 'Universitas Tarumanagara', 'gpa' => 3.62,
            'bar_exam_cohort' => 'Gelombang II 2025', 'bar_exam_graduation_year' => 2025,
            'law_firm_id' => $wibisono->id, 'supervising_lawyer_id' => $hendra->id,
            'placement_area' => 'Kontrak', 'internship_started_on' => now()->subMonths(11),
        ]);
        $seedLogbook($nadia, 11, 1.0, $hendra);

        // ---- Fajar Nugroho (3/24 bulan, logbook terlambat 6 hari) ----
        $fajar = $makeCalon('Fajar Nugroho, S.H.', 'fajar@peradijakbar.test', 'CA-2026-0031', [
            'national_id_number' => '3171013344550004', 'university' => 'Universitas Trisakti', 'gpa' => 3.30,
            'bar_exam_cohort' => 'Gelombang I 2026', 'bar_exam_graduation_year' => 2026,
            'law_firm_id' => $wibisono->id, 'supervising_lawyer_id' => $hendra->id,
            'placement_area' => 'Litigasi Perdata', 'internship_started_on' => now()->subMonths(3),
        ]);
        LogbookEntry::create([
            'candidate_advocate_id' => $fajar->id, 'entry_date' => now()->subDays(7),
            'activity_type' => 'Riset hukum', 'hours' => 4, 'description' => 'Riset awal perkara litigasi perdata.',
            'status' => 'APPROVED',
        ]);
        MonthlyLogbookSummary::create([
            'candidate_advocate_id' => $fajar->id, 'month' => now()->month, 'year' => now()->year,
            'status' => 'PENDING_SIGNATURE',
        ]);

        // ---- Applicants pending both Admin verification and Firm review ----
        $maya = $makeCalon('Maya Ramadhani, S.H.', 'maya@peradijakbar.test', 'CA-2026-0058', [
            'national_id_number' => '3172014455660005', 'university' => 'Universitas Indonesia', 'gpa' => 3.61,
            'bar_exam_cohort' => 'Gelombang II 2025', 'bar_exam_graduation_year' => 2025, 'verification_status' => 'PENDING',
        ]);
        CandidateVerificationChecklist::seedDemoState($maya, [
            'Sertifikat PKPA cocok data admisi' => true,
            'Sertifikat Lulus UPA terverifikasi' => true,
            'Ijazah S.H. terbaca jelas' => true,
            'Pasfoto latar merah sesuai ketentuan' => true,
            'Data profil lengkap (NIK & universitas)' => true,
            'Status keanggotaan DPC aktif' => true,
        ]);
        InternshipApplication::create(['candidate_advocate_id' => $maya->id, 'job_posting_id' => $jobPostingWibisono->id, 'status' => 'INTERVIEW', 'applied_on' => now()->subDays(4)]);

        $bagus = $makeCalon('Bagus Setiawan, S.H.', 'bagus@peradijakbar.test', 'CA-2025-0349', [
            'national_id_number' => '3174012233440006', 'university' => 'Universitas Trisakti', 'gpa' => 3.42,
            'bar_exam_cohort' => 'Gelombang II 2025', 'bar_exam_graduation_year' => 2025, 'verification_status' => 'NEEDS_CORRECTION',
        ]);
        CandidateVerificationChecklist::seedDemoState($bagus, [
            'Sertifikat PKPA cocok data admisi' => true,
            'Sertifikat Lulus UPA terverifikasi' => true,
            'Ijazah S.H. terbaca jelas' => true,
            'Pasfoto latar merah sesuai ketentuan' => false,
            'Data profil lengkap (NIK & universitas)' => true,
            'Status keanggotaan DPC aktif' => true,
        ]);
        InternshipApplication::create(['candidate_advocate_id' => $bagus->id, 'job_posting_id' => $jobPostingWibisono->id, 'status' => 'CV_REVIEW', 'applied_on' => now()->subDays(6)]);

        $laras = $makeCalon('Laras Nuraini, S.H.', 'laras@peradijakbar.test', 'CA-2026-0072', [
            'national_id_number' => '3173015566770007', 'university' => 'Universitas Tarumanagara', 'gpa' => 3.70,
            'bar_exam_cohort' => 'Gelombang I 2026', 'bar_exam_graduation_year' => 2026, 'verification_status' => 'PENDING',
        ]);
        CandidateVerificationChecklist::seedDemoState($laras, [
            'Sertifikat PKPA cocok data admisi' => true,
            'Sertifikat Lulus UPA terverifikasi' => true,
            'Ijazah S.H. terbaca jelas' => true,
            'Pasfoto latar merah sesuai ketentuan' => true,
            'Data profil lengkap (NIK & universitas)' => true,
            'Status keanggotaan DPC aktif' => false,
        ]);
        InternshipApplication::create(['candidate_advocate_id' => $laras->id, 'job_posting_id' => $jobPostingWibisono->id, 'status' => 'CV_REVIEW', 'applied_on' => now()->subDays(2)]);

        // ---- Prasetya & Co. applicant, not accepted ----
        $rina = $makeCalon('Rina Marlina, S.H.', 'rina@peradijakbar.test', 'CA-2025-0201', [
            'national_id_number' => '3171019988770008', 'university' => 'Universitas Trisakti', 'gpa' => 3.20,
            'bar_exam_cohort' => 'Gelombang I 2025', 'bar_exam_graduation_year' => 2025,
        ]);
        InternshipApplication::create(['candidate_advocate_id' => $rina->id, 'job_posting_id' => $jobPostingWibisono->id, 'status' => 'REJECTED', 'applied_on' => now()->subDays(30)]);

        // ---- Andi's own application history (for "InternshipApplication Saya") ----
        InternshipApplication::create(['candidate_advocate_id' => $andi->id, 'job_posting_id' => $jobPostingWibisono->id, 'status' => 'ACCEPTED', 'applied_on' => now()->subMonths(14)->subDays(3)]);

        // ---- Santika, Hartono & Partners pemagang (for compliance %) ----
        $indra = $makeCalon('Indra Kurniawan, S.H.', 'indra@peradijakbar.test', 'CA-2025-0155', [
            'national_id_number' => '3171017788990009', 'university' => 'Universitas Indonesia', 'gpa' => 3.50,
            'bar_exam_cohort' => 'Gelombang I 2025', 'bar_exam_graduation_year' => 2025,
            'law_firm_id' => $santika->id, 'supervising_lawyer_id' => $pendampingSantika->id,
            'placement_area' => 'Litigasi Korporasi', 'internship_started_on' => now()->subMonths(16),
        ]);
        $seedLogbook($indra, 16, 0.87, $pendampingSantika);

        // ---- Dewi Anggraini: lulus audit, 24/24 bulan ----
        $dewi = $makeCalon('Dewi Anggraini, S.H.', 'dewi@peradijakbar.test', 'CA-2024-0090', [
            'national_id_number' => '3171011122330010', 'university' => 'Universitas Indonesia', 'gpa' => 3.75,
            'bar_exam_cohort' => 'Gelombang I 2024', 'bar_exam_graduation_year' => 2024,
            'law_firm_id' => $santika->id, 'supervising_lawyer_id' => $pendampingSantika->id,
            'placement_area' => 'Litigasi Korporasi', 'internship_started_on' => now()->subMonths(24),
            'membership_status' => 'INACTIVE',
        ]);
        $seedLogbook($dewi, 24, 1.0, $pendampingSantika);
        FinalAudit::create(['candidate_advocate_id' => $dewi->id, 'status' => 'PASSED', 'notes' => 'Logbook lengkap, sertifikat terbit.', 'audited_at' => now()->subDays(5)]);
        foreach ($berkasTemplate($dewi, [
            5 => ['document_type' => 'sertifikat_selesai_magang', 'source_label' => 'Magang · diterbitkan kantor hukum', 'status' => 'COMPLETE', 'file_size_label' => '0,4 MB'],
            6 => ['document_type' => 'surat_rekomendasi_dpc', 'source_label' => 'Magang · setelah audit akhir', 'status' => 'COMPLETE', 'file_size_label' => '0,3 MB'],
        ]) as $b) {
            OathDocument::create(array_merge(['candidate_advocate_id' => $dewi->id], $b));
        }

        // ---- Yoga Permana: 24/24 bulan, berkas kurang ----
        $yoga = $makeCalon('Yoga Permana, S.H.', 'yoga@peradijakbar.test', 'CA-2024-0102', [
            'national_id_number' => '3171014455660011', 'university' => 'Universitas Trisakti', 'gpa' => 3.40,
            'bar_exam_cohort' => 'Gelombang II 2024', 'bar_exam_graduation_year' => 2024,
            'law_firm_id' => $ardiansyah->id, 'placement_area' => 'Hukum Keluarga',
            'internship_started_on' => now()->subMonths(24), 'membership_status' => 'INACTIVE',
        ]);
        $seedLogbook($yoga, 24, 0.64, null);
        FinalAudit::create(['candidate_advocate_id' => $yoga->id, 'status' => 'INCOMPLETE_DOCUMENTS', 'notes' => 'Sertifikat selesai magang belum diunggah.']);

        // ---- LBH Trisakti pemagang (for compliance %) ----
        $sinta = $makeCalon('Sinta Wulandari, S.H.', 'sinta@peradijakbar.test', 'CA-2025-0233', [
            'national_id_number' => '3171016677880012', 'university' => 'Universitas Trisakti', 'gpa' => 3.45,
            'bar_exam_cohort' => 'Gelombang II 2025', 'bar_exam_graduation_year' => 2025,
            'law_firm_id' => $lbhTrisakti->id, 'placement_area' => 'Bantuan Hukum',
            'internship_started_on' => now()->subMonths(9),
        ]);
        $seedLogbook($sinta, 9, 0.78, null);

        // ---- Admin DPC login ----
        User::create([
            'name' => 'Sekretariat DPC', 'email' => 'admin@peradijakbar.test',
            'password' => $password, 'role' => 'admin_dpc', 'email_verified_at' => now(),
        ]);

        Banner::create([
            'created_by_user_id' => User::where('email', 'admin@peradijakbar.test')->value('id'),
            'target' => Banner::TARGET_CALON,
            'title' => 'Pengumuman DPC Jakarta Barat',
            'body' => '<p>Harap lengkapi verifikasi admisi sebelum mengajukan lamaran magang.</p>',
            'published_at' => now(),
        ]);

        $this->call(ExtraDemoSeeder::class);
        $this->seedDemoMatches();
    }

    private function seedDemoMatches(): void
    {
        $adminId = User::where('email', 'admin@peradijakbar.test')->value('id');
        $wibisono = LawFirm::where('name', 'Wibisono & Rekan')->firstOrFail();
        $santika = LawFirm::where('name', 'Santika, Hartono & Partners')->firstOrFail();
        $ardiansyah = LawFirm::where('name', 'Kantor Hukum Ardiansyah')->firstOrFail();
        $lbh = LawFirm::where('name', 'LBH Kampus Universitas Trisakti')->firstOrFail();

        $attach = function (array $emails, LawFirm $firm) use ($adminId) {
            $jobIds = $firm->jobPostings()->where('status', 'ACTIVE')->pluck('id');
            $ids = CandidateAdvocate::whereHas('user', fn ($q) => $q->whereIn('email', $emails))->pluck('id');
            foreach ($ids as $id) {
                CandidateAdvocate::find($id)->matchedJobPostings()->syncWithoutDetaching(
                    $jobIds->mapWithKeys(fn ($jobId) => [$jobId => ['matched_by' => $adminId]])->all()
                );
            }
        };

        $attach([
            'andi@peradijakbar.test',
            'dimas@peradijakbar.test',
            'putri@peradijakbar.test',
            'maya@peradijakbar.test',
            'bagus@peradijakbar.test',
            'laras@peradijakbar.test',
            'rina@peradijakbar.test',
            'rizky@peradijakbar.test',
            'nadia@peradijakbar.test',
            'fajar@peradijakbar.test',
            'hana@peradijakbar.test',
        ], $wibisono);
        $attach([
            'citra@peradijakbar.test',
            'indra@peradijakbar.test',
            'dewi@peradijakbar.test',
        ], $santika);
        $attach([
            'yoga@peradijakbar.test',
            'budi@peradijakbar.test',
        ], $ardiansyah);
        $attach(['sinta@peradijakbar.test'], $lbh);
    }
}
