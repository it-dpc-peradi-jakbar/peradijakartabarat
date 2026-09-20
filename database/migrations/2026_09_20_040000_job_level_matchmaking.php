<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_postings', function (Blueprint $table) {
            $table->string('kabupaten_kota_kode', 5)->nullable()->after('provides_transport');
        });

        Schema::table('candidate_advocates', function (Blueprint $table) {
            $table->json('work_reference')->nullable()->after('kecamatan_kode');
            $table->boolean('wants_transport')->default(false)->after('work_reference');
        });

        Schema::create('candidate_job_posting_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_advocate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_posting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('matched_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['candidate_advocate_id', 'job_posting_id'], 'ca_job_match_unique');
        });

        $firmKabupaten = DB::table('law_firms')->pluck('kabupaten_kota_kode', 'id');
        foreach (DB::table('job_postings')->whereNull('kabupaten_kota_kode')->get() as $job) {
            $kode = $firmKabupaten[$job->law_firm_id] ?? null;
            if ($kode) {
                DB::table('job_postings')->where('id', $job->id)->update(['kabupaten_kota_kode' => $kode]);
            }
        }

        if (Schema::hasTable('candidate_law_firm_matches')) {
            $now = now();
            foreach (DB::table('candidate_law_firm_matches')->get() as $match) {
                $jobIds = DB::table('job_postings')
                    ->where('law_firm_id', $match->law_firm_id)
                    ->where('status', 'ACTIVE')
                    ->pluck('id');
                foreach ($jobIds as $jobId) {
                    DB::table('candidate_job_posting_matches')->insertOrIgnore([
                        'candidate_advocate_id' => $match->candidate_advocate_id,
                        'job_posting_id' => $jobId,
                        'matched_by' => $match->matched_by,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            Schema::drop('candidate_law_firm_matches');
        }
    }

    public function down(): void
    {
        Schema::create('candidate_law_firm_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_advocate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('law_firm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('matched_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['candidate_advocate_id', 'law_firm_id'], 'ca_firm_match_unique');
        });

        Schema::dropIfExists('candidate_job_posting_matches');

        Schema::table('candidate_advocates', function (Blueprint $table) {
            $table->dropColumn(['work_reference', 'wants_transport']);
        });

        Schema::table('job_postings', function (Blueprint $table) {
            $table->dropColumn('kabupaten_kota_kode');
        });
    }
};
