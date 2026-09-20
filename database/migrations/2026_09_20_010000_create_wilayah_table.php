<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wilayah', function (Blueprint $table) {
            $table->string('kode', 13)->primary();
            $table->string('nama', 100);
            $table->string('level', 20);
            $table->index('level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wilayah');
    }
};
