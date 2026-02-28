<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReadingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comic extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'publisher',
        'start_year',
        'issue_count',
        'description',
        'cover_url',
        'comicvine_volume_id',
        'comicvine_url',
        'creators',
        'characters',
        'status',
        'rating',
        'date_started',
        'date_finished',
        'date_added',
        'notes',
        'review',
        'metadata_fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReadingStatus::class,
            'rating' => 'integer',
            'start_year' => 'integer',
            'issue_count' => 'integer',
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

    public function issues(): HasMany
    {
        return $this->hasMany(ComicIssue::class);
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeWithStatus($query, ReadingStatus $status)
    {
        return $query->where('status', $status);
    }

    public function getCreatorsArrayAttribute(): array
    {
        if (empty($this->creators)) {
            return [];
        }

        return array_map('trim', explode(',', $this->creators));
    }

    public function getCharactersArrayAttribute(): array
    {
        if (empty($this->characters)) {
            return [];
        }

        return array_map('trim', explode(',', $this->characters));
    }
}
