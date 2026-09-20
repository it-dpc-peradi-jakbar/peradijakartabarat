<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class LawFirm extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'provinsi_kode',
        'kabupaten_kota_kode',
        'kecamatan_kode',
        'ministry_registration_number',
        'is_equivalent_law_firm',
        'max_quota',
        'verification_status',
        'verified_at',
    ];

    protected $casts = [
        'is_equivalent_law_firm' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function supervisingLawyers(): HasMany
    {
        return $this->hasMany(SupervisingLawyer::class);
    }

    public function candidateAdvocates(): HasMany
    {
        return $this->hasMany(CandidateAdvocate::class);
    }

    public function jobPostings(): HasMany
    {
        return $this->hasMany(JobPosting::class);
    }

    public function checklistItems(): MorphMany
    {
        return $this->morphMany(VerificationChecklist::class, 'checkable');
    }

    public function matchedCandidates(): BelongsToMany
    {
        return $this->belongsToMany(CandidateAdvocate::class, 'candidate_law_firm_matches')
            ->withPivot('matched_by')
            ->withTimestamps();
    }

    public function wilayahLabel(): string
    {
        return \App\Support\WilayahHierarchy::label(
            $this->provinsi_kode,
            $this->kabupaten_kota_kode,
            $this->kecamatan_kode
        );
    }

    public function kuotaTerpakai(): int
    {
        return $this->candidateAdvocates()->where('membership_status', 'ACTIVE')->count();
    }

    public function kuotaTersisa(): int
    {
        return max(0, $this->max_quota - $this->kuotaTerpakai());
    }

    public function kepatuhanLogbookPersen(): int
    {
        $entries = LogbookEntry::whereIn('candidate_advocate_id', $this->candidateAdvocates()->pluck('id'));
        $total = $entries->count();
        if ($total === 0) {
            return 0;
        }
        $disetujui = (clone $entries)->where('status', 'APPROVED')->count();

        return (int) round(($disetujui / $total) * 100);
    }
}
