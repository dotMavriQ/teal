<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WatchingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Anime extends Model
{
    protected $table = 'anime';

    protected $fillable = [
        'user_id',
        'title',
        'original_title',
        'poster_url',
        'description',
        'year',
        'episodes_total',
        'episodes_watched',
        'runtime_minutes',
        'genres',
        'studios',
        'media_type',
        'status',
        'rating',
        'mal_id',
        'mal_score',
        'mal_url',
        'date_started',
        'date_finished',
        'date_added',
        'notes',
        'review',
        'tags',
        'metadata_fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => WatchingStatus::class,
            'rating' => 'integer',
            'mal_score' => 'decimal:1',
            'year' => 'integer',
            'episodes_total' => 'integer',
            'episodes_watched' => 'integer',
            'runtime_minutes' => 'integer',
            'date_started' => 'date',
            'date_finished' => 'date',
            'date_added' => 'date',
            'metadata_fetched_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeWithStatus($query, WatchingStatus $status)
    {
        return $query->where('status', $status);
    }

    public function getGenreListAttribute(): array
    {
        if (empty($this->genres)) {
            return [];
        }

        return collect(explode(',', $this->genres))
            ->map(fn ($genre) => trim($genre))
            ->filter(fn ($genre) => $genre !== '')
            ->values()
            ->all();
    }

    public function getStudioListAttribute(): array
    {
        if (empty($this->studios)) {
            return [];
        }

        return collect(explode(',', $this->studios))
            ->map(fn ($studio) => trim($studio))
            ->filter(fn ($studio) => $studio !== '')
            ->values()
            ->all();
    }

    public function getRuntimeFormattedAttribute(): ?string
    {
        if ($this->runtime_minutes === null) {
            return null;
        }

        $hours = intdiv($this->runtime_minutes, 60);
        $minutes = $this->runtime_minutes % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        }

        if ($hours > 0) {
            return "{$hours}h";
        }

        return "{$minutes}m";
    }

    public static function getAllGenresForUser(int $userId): array
    {
        return static::where('user_id', $userId)
            ->whereNotNull('genres')
            ->pluck('genres')
            ->flatMap(fn ($s) => explode(',', $s))
            ->map(fn ($s) => trim($s))
            ->filter(fn ($s) => $s !== '')
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    public static function getAllStudiosForUser(int $userId): array
    {
        return static::where('user_id', $userId)
            ->whereNotNull('studios')
            ->pluck('studios')
            ->flatMap(fn ($s) => explode(',', $s))
            ->map(fn ($s) => trim($s))
            ->filter(fn ($s) => $s !== '')
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}
