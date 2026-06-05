<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ListeningStatus;
use Database\Factories\ConcertFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property ListeningStatus $status
 * @property Carbon|null $event_date
 * @property array<int, mixed>|null $setlist
 */
class Concert extends Model
{
    /** @use HasFactory<ConcertFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'artist',
        'tour_name',
        'venue',
        'city',
        'country',
        'event_date',
        'setlist',
        'cover_url',
        'status',
        'rating',
        'setlist_fm_id',
        'artist_mbid',
        'notes',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'status' => ListeningStatus::class,
            'setlist' => 'array',
            'rating' => 'integer',
            'event_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
