<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Episode extends Model
{
    protected $fillable = [
        'show_id',
        'user_id',
        'title',
        'season_number',
        'episode_number',
        'imdb_id',
        'imdb_url',
        'director',
        'runtime_minutes',
        'rating',
        'imdb_rating',
        'num_votes',
        'date_rated',
        'release_date',
        'date_watched',
        'genres',
        'year',
    ];

    protected function casts(): array
    {
        return [
            'season_number' => 'integer',
            'episode_number' => 'integer',
            'rating' => 'integer',
            'imdb_rating' => 'decimal:1',
            'num_votes' => 'integer',
            'runtime_minutes' => 'integer',
            'year' => 'integer',
            'date_rated' => 'date',
            'release_date' => 'date',
            'date_watched' => 'date',
        ];
    }

    public function show(): BelongsTo
    {
        return $this->belongsTo(Show::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getSeasonEpisodeLabelAttribute(): ?string
    {
        if ($this->season_number === null || $this->episode_number === null) {
            return null;
        }

        return 'S' . str_pad((string) $this->season_number, 2, '0', STR_PAD_LEFT)
            . 'E' . str_pad((string) $this->episode_number, 2, '0', STR_PAD_LEFT);
    }
}
