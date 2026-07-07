<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Resolves a third-party credential following the instance policy:
 *
 *   1. the authenticated user's own key for the slug (unless policy = shared)
 *   2. the instance-level value from config/services.php (unless policy = byo)
 *   3. null — the feature is unconfigured and should degrade gracefully
 *
 * Keys are always scoped to Auth::user(); a caller can never resolve another
 * user's key. Outside a request (e.g. queue jobs) there is no authenticated
 * user, so resolution falls back to the instance key.
 */
final class ApiKey
{
    /**
     * @return array<string, string> map of credential slug => config path
     */
    public static function slugMap(): array
    {
        /** @var array<string, array{fields: array<string, array{config: string}>}> $services */
        $services = config('api_keys.services', []);

        $map = [];
        foreach ($services as $service) {
            foreach ($service['fields'] as $slug => $field) {
                $map[$slug] = $field['config'];
            }
        }

        return $map;
    }

    public static function resolve(string $slug): ?string
    {
        $configPath = self::slugMap()[$slug] ?? null;
        if ($configPath === null) {
            return null;
        }

        /** @var string $policy */
        $policy = config('api_keys.policy', 'both');

        if ($policy !== 'shared') {
            $user = Auth::user();
            if ($user instanceof User) {
                $userKey = $user->apiKeyFor($slug);
                if ($userKey !== null && $userKey !== '') {
                    return $userKey;
                }
            }
        }

        if ($policy === 'byo') {
            return null;
        }

        $value = config($configPath);

        return is_string($value) && $value !== '' ? $value : null;
    }
}
