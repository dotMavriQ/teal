<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OwnershipStatus;
use App\Enums\PlayingStatus;
use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GameFactory extends Factory
{
    protected $model = Game::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(2),
            'status' => PlayingStatus::Backlog,
            'ownership' => OwnershipStatus::NotOwned,
        ];
    }
}
