<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReadingStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property ReadingStatus $status
 * @property \Illuminate\Support\Carbon|null $published_date
 * @property \Illuminate\Support\Carbon|null $date_started
 * @property \Illuminate\Support\Carbon|null $date_finished
 * @property \Illuminate\Support\Carbon|null $date_added
 */
class Book extends Model
{
    /** @use HasFactory<\Database\Factories\BookFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'author',
        'isbn',
        'isbn13',
        'asin',
        'cover_url',
        'description',
        'page_count',
        'current_page',
        'published_date',
        'publisher',
        'goodreads_id',
        'status',
        'queue_position',
        'rating',
        'avg_rating',
        'num_ratings',
        'date_pub',
        'date_pub_edition',
        'date_started',
        'date_finished',
        'date_added',
        'shelves',
        'notes',
        'review',
        'comments',
        'votes',
        'owned',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'status' => ReadingStatus::class,
            'rating' => 'integer',
            'avg_rating' => 'decimal:2',
            'num_ratings' => 'integer',
            'page_count' => 'integer',
            'current_page' => 'integer',
            'comments' => 'integer',
            'votes' => 'integer',
            'owned' => 'boolean',
            'published_date' => 'date',
            'date_started' => 'date',
            'date_finished' => 'date',
            'date_added' => 'date',
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
     * @return BelongsToMany<Shelf, $this>
     */
    public function bookShelves(): BelongsToMany
    {
        return $this->belongsToMany(Shelf::class)->withTimestamps();
    }

    /**
     * @param  Builder<Book>  $query
     * @return Builder<Book>
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * @param  Builder<Book>  $query
     * @return Builder<Book>
     */
    public function scopeWithStatus(Builder $query, ReadingStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Get the published date, falling back to date_pub (year only) if published_date is empty.
     * This handles cases where Goodreads data has only year (e.g., "2009") in date_pub.
     */
    public function getPublishedYearAttribute(): ?string
    {
        if ($this->published_date) {
            return $this->published_date->format('Y');
        }

        // Fallback to date_pub if it's just a year
        if (is_string($this->date_pub) && preg_match('/^\d{4}/', $this->date_pub, $matches)) {
            return $matches[0];
        }

        return null;
    }

    /**
     * Status values that should be filtered out from tags.
     *
     * @var list<string>
     */
    protected static array $statusShelves = ['read', 'to-read', 'currently-reading', 'want-to-read'];

    /**
     * Get tags from the shelves field, excluding status values.
     *
     * @return array<string>
     */
    public function getTagsAttribute(): array
    {
        $shelves = $this->shelves;
        if (! is_string($shelves) || $shelves === '') {
            return [];
        }

        return collect(explode(',', $shelves))
            ->map(fn ($tag) => trim($tag))
            ->filter(fn ($tag) => $tag !== '' && ! in_array(strtolower($tag), self::$statusShelves))
            ->values()
            ->all();
    }

    /**
     * Set tags by updating the shelves field, preserving any status value.
     *
     * @param  array<string>  $tags
     */
    public function setTagsFromArray(array $tags): void
    {
        $currentParts = is_string($this->shelves) && $this->shelves !== '' ? explode(',', $this->shelves) : [];
        $statusPart = null;

        // Find and preserve status value
        foreach ($currentParts as $part) {
            if (in_array(strtolower(trim($part)), self::$statusShelves)) {
                $statusPart = trim($part);
                break;
            }
        }

        // Build new shelves string
        $newParts = $statusPart ? [$statusPart] : [];
        foreach ($tags as $tag) {
            $tag = trim($tag);
            if ($tag !== '' && ! in_array(strtolower($tag), self::$statusShelves)) {
                $newParts[] = $tag;
            }
        }

        $this->shelves = implode(', ', $newParts) ?: null;
    }

    /**
     * Get all unique tags across all books for a user.
     *
     * @return array<string>
     */
    public static function getAllTagsForUser(int $userId): array
    {
        return static::where('user_id', $userId)
            ->whereNotNull('shelves')
            ->pluck('shelves')
            ->flatMap(fn ($s) => is_string($s) ? explode(',', $s) : [])
            ->map(fn ($s) => trim($s))
            ->filter(fn ($s) => $s !== '' && ! in_array(strtolower($s), self::$statusShelves))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Get a thumbnail version of the cover URL.
     * For GoodReads URLs, adds size modifier. For local URLs, returns as-is.
     */
    public function getThumbnailUrl(int $size = 50): ?string
    {
        $coverUrl = $this->cover_url;
        if (! is_string($coverUrl) || $coverUrl === '') {
            return null;
        }

        // Local storage - return as-is (could add thumbnail generation later)
        if (str_starts_with($coverUrl, '/storage/')) {
            return $coverUrl;
        }

        // GoodReads URLs - add size modifier
        if (str_contains($coverUrl, 'gr-assets.com')) {
            // Transform: image.jpg -> image._SX{size}_.jpg
            return preg_replace(
                '/(\.\w+)$/',
                "._SX{$size}_$1",
                $coverUrl
            );
        }

        // Other external URLs - return as-is
        return $coverUrl;
    }

    /**
     * Get reading progress as a percentage (0-100).
     */
    public function getProgressPercentageAttribute(): ?int
    {
        if ($this->current_page === null || $this->page_count === null || $this->page_count === 0) {
            return null;
        }

        return min(100, (int) round(($this->current_page / $this->page_count) * 100));
    }

    /**
     * Check if this book has progress tracking available.
     */
    public function getCanTrackProgressAttribute(): bool
    {
        return $this->page_count !== null && $this->page_count > 0;
    }
}
