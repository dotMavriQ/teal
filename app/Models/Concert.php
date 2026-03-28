<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ListeningStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Concert extends Model
{
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

    protected function casts(): array
    {
        return [
            'status' => ListeningStatus::class,
            'setlist' => 'array',
            'rating' => 'integer',
            'event_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
