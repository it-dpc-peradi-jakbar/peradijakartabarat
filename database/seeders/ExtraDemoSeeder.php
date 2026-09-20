<?php

namespace Database\Seeders;

use App\Models\CandidateAdvocate;
use App\Models\InternshipApplication;
use App\Models\JobPosting;
use App\Models\LawFirm;
use App\Models\LogbookEntry;
use App\Models\MonthlyLogbookSummary;
use App\Models\SupervisingLawyer;
use App\Models\User;
use App\Support\CandidateVerificationChecklist;
use App\Support\WilayahHierarchy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Extra dummy rows for local / Docker testing (all logins use DemoUserCatalog::PASSWORD).
 */
class ExtraDemoSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make(DemoUserCatalog::PASSWORD);

        $wibisono = LawFirm::where('name', 'Wibisono & Rekan')->firstOrFail();
        $santika = LawFirm::where('name', 'Santika, Hartono & Partners')->firstOrFail();
        $ardiansyah = LawFirm::where('name', 'Kantor Hukum Ardiansyah')->firstOrFail();
        $lbh = LawFirm::where('name', 'LBH Kampus Universitas Trisakti')->firstOrFail();
        $wijaya = LawFirm::where('name', 'Wijaya Legal Consult')->firstOrFail();
        $kusuma = LawFirm::where('name', 'Kusuma & Associates')->firstOrFail();

        $hendra = SupervisingLawyer::where('bar_membership_number', 'KTA-DPC-JB-00214')->firstOrFail();
        $pendampingSantika = SupervisingLawyer::where('bar_membership_number', 'KTA-DPC-JB-00187')->firstOrFail();

        $this->attachFirmLogin($pendampingSantika, 'Ratna Santika, S.H., M.Kn.', 'santika@peradijakbar.test', $password);

        $pendampingArdiansyah = SupervisingLawyer::firstOrCreate(
            ['bar_membership_number' => 'KTA-DPC-JB-00301'],
            [
                'law_firm_id' => $ardiansyah->id,
                'name' => 'Agus Ardiansyah, S.H.',
                'bar_membership_active' => true,
                'years_of_experience' => 9,
            ]
        );
        $this->attachFirmLogin($pendampingArdiansyah, 'Agus Ardiansyah, S.H.', 'ardiansyah@peradijakbar.test', $password);

        $pendampingLbh = SupervisingLawyer::firstOrCreate(
            ['bar_membership_number' => 'KTA-DPC-JB-00412'],
            [
                'law_firm_id' => $lbh->id,
                'name' => 'Sari Melati, S.H.',
                'bar_membership_active' => true,
                'years_of_experience' => 7,
            ]
        );
        $this->attachFirmLogin($pendampingLbh, 'Sari Melati, S.H.', 'lbh@peradijakbar.test', $password);

        $pendampingWijaya = SupervisingLawyer::firstOrCreate(
            ['bar_membership_number' => 'KTA-DPC-JB-00501'],
            [
                'law_firm_id' => $wijaya->id,
                'name' => 'Tono Wijaya, S.H.',
                'bar_membership_active' => true,
                'years_of_experience' => 8,
            ]
        );
        $this->attachFirmLogin($pendampingWijaya, 'Tono Wijaya, S.H.', 'wijaya@peradijakbar.test', $password);

        $pendampingKusuma = SupervisingLawyer::firstOrCreate(
            ['bar_membership_number' => 'KTA-DPC-JB-00601'],
            [
                'law_firm_id' => $kusuma->id,
                'name' => 'Lina Kusuma, S.H.',
                'bar_membership_active' => true,
                'years_of_experience' => 6,
            ]
        );
        $this->attachFirmLogin($pendampingKusuma, 'Lina Kusuma, S.H.', 'kusuma@peradijakbar.test', $password);

        JobPosting::firstOrCreate(
            [
                'law_firm_id' => $wibisono->id,
                'title' => 'Magang Calon Advokat — Prodeo & Pidana',
            ],
            [
                'description' => 'Pendampingan perkara pidana dan bantuan hukum prodeo di PN Jakarta Barat.',
                'practice_areas' => ['Pidana', 'Prodeo', 'Full-time'],
                'quota' => 4,
                'status' => 'ACTIVE',
            ]
        );

        $jobWibisono = JobPosting::where('law_firm_id', $wibisono->id)
            ->where('title', 'Magang Calon Advokat — Litigasi Perdata & Kepailitan')
            ->firstOrFail();
        $jobSantika = JobPosting::where('law_firm_id', $santika->id)->firstOrFail();
        $jobArdiansyah = JobPosting::where('law_firm_id', $ardiansyah->id)->firstOrFail();

        $dimas = $this->makeCalon('Dimas Pratama, S.H.', 'dimas@peradijakbar.test', 'CA-2026-0101', $password, [
            'national_id_number' => '3171021112220013',
            'university' => 'Universitas Indonesia',
            'gpa' => 3.58,
            'bar_exam_cohort' => 'Gelombang I 2026',
            'bar_exam_graduation_year' => 2026,
        ]);
        CandidateVerificationChecklist::seedDemoState($dimas, $this->allChecked());
        InternshipApplication::firstOrCreate(
            ['candidate_advocate_id' => $dimas->id, 'job_posting_id' => $jobWibisono->id],
            ['status' => InternshipApplication::STATUS_SUBMITTED, 'applied_on' => now()->subDays(1)]
        );

        $this->makeCalon('Putri Handayani, S.H.', 'putri@peradijakbar.test', 'CA-2026-0102', $password, [
            'national_id_number' => '3171023334440014',
            'university' => 'Universitas Tarumanagara',
            'gpa' => 3.66,
            'bar_exam_cohort' => 'Gelombang I 2026',
            'bar_exam_graduation_year' => 2026,
        ]);

        $eko = $this->makeCalon('Eko Santoso, S.H.', 'eko@peradijakbar.test', 'CA-2026-0103', $password, [
            'national_id_number' => null,
            'university' => null,
            'verification_status' => 'PENDING',
            'bar_exam_cohort' => 'Gelombang I 2026',
            'bar_exam_graduation_year' => 2026,
        ]);
        CandidateVerificationChecklist::seedDemoState($eko, [
            'Sertifikat PKPA cocok data admisi' => false,
            'Sertifikat Lulus UPA terverifikasi' => false,
            'Ijazah S.H. terbaca jelas' => false,
            'Pasfoto latar merah sesuai ketentuan' => false,
            'Data profil lengkap (NIK & universitas)' => false,
            'Status keanggotaan DPC aktif' => false,
        ]);

        $hana = $this->makeCalon('Hana Wijaya, S.H.', 'hana@peradijakbar.test', 'CA-2026-0104', $password, [
            'national_id_number' => '3171025556660015',
            'university' => 'Universitas Trisakti',
            'gpa' => 3.41,
            'bar_exam_cohort' => 'Gelombang II 2025',
            'bar_exam_graduation_year' => 2025,
            'law_firm_id' => $wibisono->id,
            'supervising_lawyer_id' => $hendra->id,
            'placement_area' => 'Litigasi Perdata',
            'internship_started_on' => now()->subMonths(6),
        ]);
        LogbookEntry::create([
            'candidate_advocate_id' => $hana->id,
            'entry_date' => now()->subDays(20),
            'activity_type' => 'Riset hukum',
            'hours' => 5,
            'description' => 'Entri terakhir — sengaja telat untuk alert monitoring DPC.',
            'status' => 'APPROVED',
        ]);
        MonthlyLogbookSummary::create([
            'candidate_advocate_id' => $hana->id,
            'month' => now()->subDays(20)->month,
            'year' => now()->subDays(20)->year,
            'status' => 'SIGNED',
            'signed_by_supervising_lawyer_id' => $hendra->id,
            'signed_at' => now()->subDays(19),
        ]);

        $budi = $this->makeCalon('Budi Hartono, S.H.', 'budi@peradijakbar.test', 'CA-2026-0105', $password, [
            'national_id_number' => '3171027778880016',
            'university' => 'Universitas Trisakti',
            'gpa' => 3.33,
            'bar_exam_cohort' => 'Gelombang II 2025',
            'bar_exam_graduation_year' => 2025,
            'law_firm_id' => $ardiansyah->id,
            'supervising_lawyer_id' => $pendampingArdiansyah->id,
            'placement_area' => 'Hukum Keluarga',
            'internship_started_on' => now()->subMonths(8),
        ]);
        $this->seedLogbook($budi, 8, 0.9, $pendampingArdiansyah);

        $citra = $this->makeCalon('Citra Lestari, S.H.', 'citra@peradijakbar.test', 'CA-2026-0106', $password, [
            'national_id_number' => '3171029990000017',
            'university' => 'Universitas Indonesia',
            'gpa' => 3.71,
            'bar_exam_cohort' => 'Gelombang I 2026',
            'bar_exam_graduation_year' => 2026,
        ]);
        CandidateVerificationChecklist::seedDemoState($citra, $this->allChecked());
        InternshipApplication::firstOrCreate(
            ['candidate_advocate_id' => $citra->id, 'job_posting_id' => $jobSantika->id],
            ['status' => InternshipApplication::STATUS_SUBMITTED, 'applied_on' => now()->subDays(2)]
        );
        InternshipApplication::firstOrCreate(
            ['candidate_advocate_id' => $citra->id, 'job_posting_id' => $jobArdiansyah->id],
            ['status' => InternshipApplication::STATUS_CV_REVIEW, 'applied_on' => now()->subDays(5)]
        );
    }

    /**
     * @param  array<string, mixed>  $attrs
     */
    private function makeCalon(string $name, string $email, string $kode, string $password, array $attrs = []): CandidateAdvocate
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => 'calon_advokat',
            'email_verified_at' => now(),
        ]);

        return CandidateAdvocate::create(array_merge(WilayahHierarchy::jakbar(), [
            'user_id' => $user->id,
            'candidate_code' => $kode,
            'membership_status' => 'ACTIVE',
            'verification_status' => 'VERIFIED',
        ], $attrs));
    }

    private function attachFirmLogin(SupervisingLawyer $lawyer, string $name, string $email, string $password): void
    {
        if ($lawyer->user_id) {
            return;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => 'law_firm',
            'email_verified_at' => now(),
        ]);
        $lawyer->update(['user_id' => $user->id]);
    }

    /**
     * @return array<string, bool>
     */
    private function allChecked(): array
    {
        return [
            'Sertifikat PKPA cocok data admisi' => true,
            'Sertifikat Lulus UPA terverifikasi' => true,
            'Ijazah S.H. terbaca jelas' => true,
            'Pasfoto latar merah sesuai ketentuan' => true,
            'Data profil lengkap (NIK & universitas)' => true,
            'Status keanggotaan DPC aktif' => true,
        ];
    }

    private function seedLogbook(CandidateAdvocate $ca, int $months, float $approvalRatio, ?SupervisingLawyer $pendamping): void
    {
        $jenis = ['Riset hukum', 'Pendampingan sidang', 'Drafting dokumen', 'Konsultasi internal'];
        for ($m = $months - 1; $m >= 0; $m--) {
            $monthDate = now()->subMonths($m);
            $entriesThisMonth = 8;
            $approvedInMonth = 0;
            for ($i = 0; $i < $entriesThisMonth; $i++) {
                $approved = $m > 0 ? true : ($i / $entriesThisMonth) < $approvalRatio;
                LogbookEntry::create([
                    'candidate_advocate_id' => $ca->id,
                    'entry_date' => $monthDate->copy()->startOfMonth()->addDays(min($i, 27)),
                    'activity_type' => $jenis[$i % count($jenis)],
                    'hours' => 4,
                    'description' => 'Catatan magang dummy untuk pengujian lokal.',
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
    }
}
