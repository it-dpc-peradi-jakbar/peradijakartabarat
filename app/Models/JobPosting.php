<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobPosting extends Model
{
    use HasFactory;

    protected $fillable = [
        'law_firm_id',
        'title',
        'description',
        'practice_areas',
        'quota',
        'status',
        'provides_transport',
        'kabupaten_kota_kode',
    ];

    protected $casts = [
        'practice_areas' => 'array',
        'provides_transport' => 'boolean',
    ];

    public function lawFirm(): BelongsTo
    {
        return $this->belongsTo(LawFirm::class);
    }

    public function internshipApplications(): HasMany
    {
        return $this->hasMany(InternshipApplication::class);
    }

    public function matchedCandidates(): BelongsToMany
    {
        return $this->belongsToMany(CandidateAdvocate::class, 'candidate_job_posting_matches')
            ->withPivot('matched_by')
            ->withTimestamps();
    }

    public function kabupatenLabel(): string
    {
        $provinsi = $this->kabupaten_kota_kode ? substr($this->kabupaten_kota_kode, 0, 2) : null;

        return \App\Support\WilayahHierarchy::label($provinsi, $this->kabupaten_kota_kode, null);
    }

    public function slotTersisa(): int
    {
        return max(0, $this->quota - $this->internshipApplications()->where('status', 'ACCEPTED')->count());
    }
}
