<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    public const TARGET_CALON = 'calon_advokat';

    public const TARGET_FIRM = 'law_firm';

    public const TARGET_PUBLIC = 'public';

    public const FORM_TARGETS = [
        self::TARGET_CALON => 'Calon advokat',
        self::TARGET_FIRM => 'Kantor hukum',
    ];

    protected $fillable = [
        'created_by_user_id',
        'target',
        'title',
        'body',
        'image_path',
        'published_at',
        'expires_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->whereNotNull('published_at')
            ->where(function (Builder $q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function scopeForTarget(Builder $query, string $target): Builder
    {
        return $query->where('target', $target);
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }

    public function isDraft(): bool
    {
        return $this->published_at === null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->lte(now());
    }

    public function deleteImage(): void
    {
        if ($this->image_path) {
            Storage::disk('public')->delete($this->image_path);
        }
    }
}
