<?php

namespace Database\Factories;

use App\Models\Episode;
use App\Models\Show;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EpisodeFactory extends Factory
{
    protected $model = Episode::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'show_id' => Show::factory(),
            'title' => $this->faker->sentence(3),
            'season_number' => $this->faker->numberBetween(1, 10),
            'episode_number' => $this->faker->numberBetween(1, 24),
            'imdb_id' => 'tt' . $this->faker->unique()->numberBetween(1000000, 9999999),
        ];
    }
}
