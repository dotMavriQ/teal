<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WatchingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Movie extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'original_title',
        'director',
        'imdb_id',
        'poster_url',
        'description',
        'year',
        'runtime_minutes',
        'genres',
        'imdb_url',
        'title_type',
        'status',
        'rating',
        'imdb_rating',
        'num_votes',
        'date_rated',
        'release_date',
        'date_watched',
        'date_added',
        'notes',
        'review',
        'metadata_fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => WatchingStatus::class,
            'rating' => 'integer',
            'imdb_rating' => 'decimal:1',
            'num_votes' => 'integer',
            'year' => 'integer',
            'runtime_minutes' => 'integer',
            'date_rated' => 'date',
            'release_date' => 'date',
            'date_watched' => 'date',
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
}
