<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WatchingStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Show extends Model
{
    /** @use HasFactory<\Database\Factories\ShowFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'original_title',
        'imdb_id',
        'imdb_url',
        'poster_url',
        'description',
        'year',
        'genres',
        'title_type',
        'status',
        'rating',
        'imdb_rating',
        'num_votes',
        'release_date',
        'date_added',
        'notes',
        'review',
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
            'imdb_rating' => 'decimal:1',
            'num_votes' => 'integer',
            'year' => 'integer',
            'release_date' => 'date',
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
     * @return HasMany<Episode, $this>
     */
    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class);
    }

    /**
     * @param  Builder<Show>  $query
     * @return Builder<Show>
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * @param  Builder<Show>  $query
     * @return Builder<Show>
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
            ->map(fn ($genre) => trim($genre))
            ->filter(fn ($genre) => $genre !== '')
            ->values()
            ->all();
    }

    /**
     * @return array<string>
     */
    public static function getAllGenresForUser(int $userId): array
    {
        return static::where('user_id', $userId)
            ->whereNotNull('genres')
            ->pluck('genres')
            ->flatMap(fn ($s) => is_string($s) ? explode(',', $s) : [])
            ->map(fn ($s) => trim($s))
            ->filter(fn ($s) => $s !== '')
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}
