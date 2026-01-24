<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Shelf extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
    ];

    protected static function booted(): void
    {
        static::creating(function (Shelf $shelf) {
            if (empty($shelf->slug)) {
                $shelf->slug = Str::slug($shelf->name);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class)->withTimestamps();
    }

    public static function findOrCreateForUser(int $userId, string $name): self
    {
        $slug = Str::slug($name);

        return self::firstOrCreate(
            ['user_id' => $userId, 'slug' => $slug],
            ['name' => $name]
        );
    }
}
