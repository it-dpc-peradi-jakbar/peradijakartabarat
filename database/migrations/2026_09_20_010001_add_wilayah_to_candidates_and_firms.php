<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidate_advocates', function (Blueprint $table) {
            $table->string('provinsi_kode', 13)->nullable()->after('university');
            $table->string('kabupaten_kota_kode', 13)->nullable()->after('provinsi_kode');
            $table->string('kecamatan_kode', 13)->nullable()->after('kabupaten_kota_kode');
        });

        Schema::table('law_firms', function (Blueprint $table) {
            $table->string('provinsi_kode', 13)->nullable()->after('address');
            $table->string('kabupaten_kota_kode', 13)->nullable()->after('provinsi_kode');
            $table->string('kecamatan_kode', 13)->nullable()->after('kabupaten_kota_kode');
        });
    }

    public function down(): void
    {
        Schema::table('candidate_advocates', function (Blueprint $table) {
            $table->dropColumn(['provinsi_kode', 'kabupaten_kota_kode', 'kecamatan_kode']);
        });

        Schema::table('law_firms', function (Blueprint $table) {
            $table->dropColumn(['provinsi_kode', 'kabupaten_kota_kode', 'kecamatan_kode']);
        });
    }
};
