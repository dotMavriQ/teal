<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\BoardGameStatus;
use App\Models\BoardGame;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BoardGameFactory extends Factory
{
    protected $model = BoardGame::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(2),
            'year_published' => $this->faker->numberBetween(1990, 2025),
            'min_players' => $this->faker->numberBetween(1, 2),
            'max_players' => $this->faker->numberBetween(3, 6),
            'status' => BoardGameStatus::Owned,
        ];
    }
}
