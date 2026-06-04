<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WatchingStatus;
use Database\Factories\AnimeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property WatchingStatus $status
 * @property Carbon|null $date_started
 * @property Carbon|null $date_finished
 * @property Carbon|null $date_added
 * @property Carbon|null $metadata_fetched_at
 */
class Anime extends Model
{
    /** @use HasFactory<AnimeFactory> */
    use HasFactory;

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

    /**
     * @return array<string, mixed>
     */
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

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param  Builder<Anime>  $query
     * @return Builder<Anime>
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * @param  Builder<Anime>  $query
     * @return Builder<Anime>
     */
    public function scopeWithStatus(Builder $query, WatchingStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * @return array<string>
     */
    public function getGenreListAttribute(): array
    {
        $genres = $this->genres;
        if (! is_string($genres) || $genres === '') {
            return [];
        }

        return collect(explode(',', $genres))
            ->map(fn ($genre): string => trim((string) $genre))
            ->filter(fn ($genre): bool => $genre !== '')
            ->values()
            ->all();
    }

    /**
     * @return array<string>
     */
    public function getStudioListAttribute(): array
    {
        $studios = $this->studios;
        if (! is_string($studios) || $studios === '') {
            return [];
        }

        return collect(explode(',', $studios))
            ->map(fn ($studio): string => trim((string) $studio))
            ->filter(fn ($studio): bool => $studio !== '')
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

    /**
     * @return array<string>
     */
    public static function getAllGenresForUser(int $userId): array
    {
        return static::where('user_id', $userId)
            ->whereNotNull('genres')
            ->pluck('genres')
            ->flatMap(fn ($s): array => is_string($s) ? explode(',', $s) : [])
            ->map(fn ($s): string => trim((string) $s))
            ->filter(fn ($s): bool => $s !== '')
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @return array<string>
     */
    public static function getAllStudiosForUser(int $userId): array
    {
        return static::where('user_id', $userId)
            ->whereNotNull('studios')
            ->pluck('studios')
            ->flatMap(fn ($s): array => is_string($s) ? explode(',', $s) : [])
            ->map(fn ($s): string => trim((string) $s))
            ->filter(fn ($s): bool => $s !== '')
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}
