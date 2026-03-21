<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OwnershipStatus;
use App\Enums\PlayingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'platform',
        'genre',
        'description',
        'cover_url',
        'release_date',
        'developer',
        'publisher',
        'status',
        'ownership',
        'rating',
        'hours_played',
        'completion_percentage',
        'rawg_id',
        'igdb_id',
        'mobygames_id',
        'date_started',
        'date_finished',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => PlayingStatus::class,
            'ownership' => OwnershipStatus::class,
            'platform' => 'array',
            'rating' => 'integer',
            'hours_played' => 'decimal:1',
            'completion_percentage' => 'integer',
            'release_date' => 'date',
            'date_started' => 'date',
            'date_finished' => 'date',
            'rawg_id' => 'integer',
            'igdb_id' => 'integer',
            'mobygames_id' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
