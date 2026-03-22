<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OwnershipStatus;
use App\Enums\PlayingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoardGame extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'genre',
        'description',
        'cover_url',
        'year_published',
        'designer',
        'publisher',
        'min_players',
        'max_players',
        'playing_time',
        'status',
        'ownership',
        'rating',
        'plays',
        'bgg_id',
        'date_started',
        'date_finished',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => PlayingStatus::class,
            'ownership' => OwnershipStatus::class,
            'genre' => 'array',
            'rating' => 'integer',
            'plays' => 'integer',
            'min_players' => 'integer',
            'max_players' => 'integer',
            'playing_time' => 'integer',
            'year_published' => 'integer',
            'bgg_id' => 'integer',
            'date_started' => 'date',
            'date_finished' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
