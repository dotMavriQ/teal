<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReadingStatus;
use Database\Factories\ComicIssueFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComicIssue extends Model
{
    /** @use HasFactory<ComicIssueFactory> */
    use HasFactory;

    protected $fillable = [
        'comic_id',
        'user_id',
        'title',
        'issue_number',
        'cover_date',
        'cover_url',
        'description',
        'comicvine_issue_id',
        'comicvine_url',
        'status',
        'rating',
        'date_read',
        'notes',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'status' => ReadingStatus::class,
            'rating' => 'integer',
            'cover_date' => 'date',
            'date_read' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Comic, $this>
     */
    public function comic(): BelongsTo
    {
        return $this->belongsTo(Comic::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
