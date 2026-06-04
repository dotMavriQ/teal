<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WatchingStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $title
 * @property string|null $original_title
 * @property string|null $director
 * @property string|null $imdb_id
 * @property string|null $poster_url
 * @property string|null $description
 * @property string|null $genres
 * @property string|null $imdb_url
 * @property string|null $title_type
 * @property string|null $show_name
 * @property string|null $notes
 * @property string|null $review
 * @property WatchingStatus $status
 * @property \Illuminate\Support\Carbon|null $release_date
 * @property \Illuminate\Support\Carbon|null $date_watched
 * @property \Illuminate\Support\Carbon|null $date_added
 * @property \Illuminate\Support\Carbon|null $date_rated
 * @property \Illuminate\Support\Carbon|null $metadata_fetched_at
 */
class Movie extends Model
{
    /** @use HasFactory<\Database\Factories\MovieFactory> */
    use HasFactory;

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
        'show_name',
        'season_number',
        'episode_number',
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
            'runtime_minutes' => 'integer',
            'season_number' => 'integer',
            'episode_number' => 'integer',
            'date_rated' => 'date',
            'release_date' => 'date',
            'date_watched' => 'date',
            'date_added' => 'date',
            'metadata_fetched_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (Movie $movie) {
            if ($movie->isLikelyEpisode() && $movie->show_name) {
                static::where('user_id', $movie->user_id)
                    ->where('title', $movie->show_name)
                    ->whereIn('title_type', ['TV Series', 'TV Mini Series'])
                    ->update(['updated_at' => now()]);
            }
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get episodes for this series.
     *
     * @return HasMany<Movie, $this>
     */
    public function episodes(): HasMany
    {
        return $this->hasMany(Movie::class, 'show_name', 'title')
            ->where('title_type', 'TV Episode')
            ->where('user_id', $this->user_id);
    }

    /**
     * @param  Builder<Movie>  $query
     * @return Builder<Movie>
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * @param  Builder<Movie>  $query
     * @return Builder<Movie>
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

    public function getSeasonEpisodeLabelAttribute(): ?string
    {
        if ($this->season_number === null || $this->episode_number === null) {
            return null;
        }

        return 'S'.str_pad((string) $this->season_number, 2, '0', STR_PAD_LEFT)
            .'E'.str_pad((string) $this->episode_number, 2, '0', STR_PAD_LEFT);
    }

    public function isEpisode(): bool
    {
        return $this->season_number !== null && $this->episode_number !== null;
    }

    public function isLikelyEpisode(): bool
    {
        return $this->isEpisode() || $this->title_type === 'TV Episode';
    }

    /**
     * Propagate show poster (and show_name) to all sibling entries missing them.
     * Only fills NULLs — never overwrites existing data.
     */
    public static function propagateShowPoster(int $userId, ?string $showName, ?string $titlePrefix, ?string $posterUrl, ?string $showNameValue = null): int
    {
        if (! $posterUrl && ! $showNameValue) {
            return 0;
        }

        $query = static::where('user_id', $userId);

        if ($showName) {
            $query->where(function ($q) use ($showName, $titlePrefix) {
                $q->where('show_name', $showName);
                if ($titlePrefix) {
                    $q->orWhere('title', 'like', $titlePrefix.':%')
                        ->orWhere('title', $titlePrefix);
                }
            });
        } elseif ($titlePrefix) {
            $query->where(function ($q) use ($titlePrefix) {
                $q->where('title', 'like', $titlePrefix.':%')
                    ->orWhere('title', $titlePrefix);
            });
        } else {
            return 0;
        }

        $updated = 0;

        if ($posterUrl) {
            $updated += $query->clone()->whereNull('poster_url')->update(['poster_url' => $posterUrl]);
        }

        if ($showNameValue) {
            $updated += $query->clone()->whereNull('show_name')->update(['show_name' => $showNameValue]);
        }

        return $updated;
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
