<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CollectionStatus;
use App\Enums\OwnershipStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Album extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'artist',
        'genre',
        'styles',
        'year',
        'format',
        'label',
        'country',
        'cover_url',
        'tracklist',
        'status',
        'ownership',
        'rating',
        'discogs_id',
        'discogs_master_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => CollectionStatus::class,
            'ownership' => OwnershipStatus::class,
            'genre' => 'array',
            'styles' => 'array',
            'tracklist' => 'array',
            'rating' => 'integer',
            'year' => 'integer',
            'discogs_id' => 'integer',
            'discogs_master_id' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
