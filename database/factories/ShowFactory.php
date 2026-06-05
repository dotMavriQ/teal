<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\WatchingStatus;
use App\Models\Show;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShowFactory extends Factory
{
    protected $model = Show::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'imdb_id' => 'tt'.$this->faker->unique()->numberBetween(1000000, 9999999),
            'status' => WatchingStatus::Watchlist,
            'year' => $this->faker->year(),
            'date_added' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function watchlist(): static
    {
        return $this->state(fn (): array => ['status' => WatchingStatus::Watchlist, 'rating' => null]);
    }

    public function watching(): static
    {
        return $this->state(fn (): array => ['status' => WatchingStatus::Watching, 'rating' => null]);
    }

    public function watched(): static
    {
        return $this->state(fn (): array => [
            'status' => WatchingStatus::Watched,
            'rating' => $this->faker->numberBetween(1, 10),
        ]);
    }
}
