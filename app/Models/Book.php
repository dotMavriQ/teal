<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReadingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'date_finished',
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
            'date_finished' => 'date',
            'date_added' => 'date',
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

    public function scopeWithStatus($query, ReadingStatus $status)
    {
        return $query->where('status', $status);
    }
}
