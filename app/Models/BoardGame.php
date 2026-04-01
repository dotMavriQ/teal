<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BoardGameStatus;
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
        'rating',
        'bgg_rating',
        'plays',
        'bgg_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => BoardGameStatus::class,
            'genre' => 'array',
            'rating' => 'integer',
            'bgg_rating' => 'decimal:2',
            'plays' => 'integer',
            'min_players' => 'integer',
            'max_players' => 'integer',
            'playing_time' => 'integer',
            'year_published' => 'integer',
            'bgg_id' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
