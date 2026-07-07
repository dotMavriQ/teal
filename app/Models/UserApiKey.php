<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserApiKeyFactory;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

/**
 * A single third-party credential owned by a user, encrypted at rest.
 *
 * @property int $id
 * @property int $user_id
 * @property string $service credential slug, e.g. "tmdb_access_token"
 * @property string|null $key decrypted on access; null if it cannot be decrypted
 */
class UserApiKey extends Model
{
    /** @use HasFactory<UserApiKeyFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'service',
        'key',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Encrypt on write, decrypt on read. A raw database dump only ever holds the
     * ciphertext. Reading a value that cannot be decrypted (e.g. APP_KEY was
     * rotated) yields null rather than throwing and taking down the app.
     *
     * @return Attribute<string|null, string>
     */
    protected function key(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value): ?string {
                if (! is_string($value) || $value === '') {
                    return null;
                }

                try {
                    return Crypt::decryptString($value);
                } catch (DecryptException) {
                    return null;
                }
            },
            set: fn (mixed $value): string => Crypt::encryptString(is_string($value) ? $value : ''),
        );
    }

    /**
     * The decrypted key, or null if unset / undecryptable.
     */
    public function plainKey(): ?string
    {
        $value = $this->key;

        return $value !== null && $value !== '' ? $value : null;
    }
}
