<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReadingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        'published_date',
        'publisher',
        'goodreads_id',
        'status',
        'rating',
        'avg_rating',
        'num_ratings',
        'date_pub',
        'date_pub_edition',
        'date_started',
        'date_recorded',
        'date_added',
        'shelves',
        'notes',
        'review',
        'comments',
        'votes',
        'owned',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReadingStatus::class,
            'rating' => 'integer',
            'avg_rating' => 'decimal:2',
            'num_ratings' => 'integer',
            'page_count' => 'integer',
            'comments' => 'integer',
            'votes' => 'integer',
            'owned' => 'boolean',
            'published_date' => 'date',
            'date_started' => 'date',
            'date_recorded' => 'date',
            'date_added' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bookShelves(): BelongsToMany
    {
        return $this->belongsToMany(Shelf::class)->withTimestamps();
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeWithStatus($query, ReadingStatus $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get a thumbnail version of the cover URL.
     * For GoodReads URLs, adds size modifier. For local URLs, returns as-is.
     */
    public function getThumbnailUrl(int $size = 50): ?string
    {
        if (empty($this->cover_url)) {
            return null;
        }

        // Local storage - return as-is (could add thumbnail generation later)
        if (str_starts_with($this->cover_url, '/storage/')) {
            return $this->cover_url;
        }

        // GoodReads URLs - add size modifier
        if (str_contains($this->cover_url, 'gr-assets.com')) {
            // Transform: image.jpg -> image._SX{size}_.jpg
            return preg_replace(
                '/(\.\w+)$/',
                "._SX{$size}_$1",
                $this->cover_url
            );
        }

        // Other external URLs - return as-is
        return $this->cover_url;
    }
}
