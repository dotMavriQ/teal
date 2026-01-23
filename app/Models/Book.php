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
        'cover_url',
        'description',
        'page_count',
        'published_date',
        'publisher',
        'goodreads_id',
        'status',
        'rating',
        'date_started',
        'date_finished',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReadingStatus::class,
            'rating' => 'integer',
            'page_count' => 'integer',
            'published_date' => 'date',
            'date_started' => 'date',
            'date_finished' => 'date',
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
