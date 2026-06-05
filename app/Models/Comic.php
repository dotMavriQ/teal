<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReadingStatus;
use Database\Factories\ComicFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property ReadingStatus $status
 * @property Carbon|null $date_started
 * @property Carbon|null $date_finished
 * @property Carbon|null $date_added
 * @property Carbon|null $metadata_fetched_at
 */
class Comic extends Model
{
    /** @use HasFactory<ComicFactory> */
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

    /**
     * @return array<string, mixed>
     */
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

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<ComicIssue, $this>
     */
    public function issues(): HasMany
    {
        return $this->hasMany(ComicIssue::class);
    }

    /**
     * @param  Builder<Comic>  $query
     * @return Builder<Comic>
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * @param  Builder<Comic>  $query
     * @return Builder<Comic>
     */
    public function scopeWithStatus(Builder $query, ReadingStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * @return array<string>
     */
    public function getCreatorsArrayAttribute(): array
    {
        $creators = $this->creators;
        if (! is_string($creators) || $creators === '') {
            return [];
        }

        return array_map(trim(...), explode(',', $creators));
    }

    /**
     * @return array<string>
     */
    public function getCharactersArrayAttribute(): array
    {
        $characters = $this->characters;
        if (! is_string($characters) || $characters === '') {
            return [];
        }

        return array_map(trim(...), explode(',', $characters));
    }
}
