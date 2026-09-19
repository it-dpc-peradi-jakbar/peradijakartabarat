<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_law_firm_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_advocate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('law_firm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('matched_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['candidate_advocate_id', 'law_firm_id'], 'ca_firm_match_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_law_firm_matches');
    }
};
