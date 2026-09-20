<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public function slotTersisa(): int
    {
        return max(0, $this->quota - $this->internshipApplications()->where('status', 'ACCEPTED')->count());
    }
}
