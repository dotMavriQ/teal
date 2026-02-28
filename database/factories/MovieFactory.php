<?php

namespace Database\Factories;

use App\Models\Movie;
use App\Models\User;
use App\Enums\WatchingStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovieFactory extends Factory
{
    protected $model = Movie::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'imdb_id' => 'tt' . $this->faker->unique()->numberBetween(1000000, 9999999),
            'status' => WatchingStatus::Watchlist,
            'year' => $this->faker->year(),
            'runtime_minutes' => $this->faker->numberBetween(80, 180),
        ];
    }
}
