<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\UserApiKey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserApiKey>
 */
class UserApiKeyFactory extends Factory
{
    protected $model = UserApiKey::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'service' => 'tmdb.access_token',
            'key' => $this->faker->sha256(),
        ];
    }
}
