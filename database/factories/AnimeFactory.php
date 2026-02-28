<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\WatchingStatus;
use App\Models\Anime;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Anime>
 */
class AnimeFactory extends Factory
{
    protected $model = Anime::class;

    public function definition(): array
    {
        $status = fake()->randomElement(WatchingStatus::cases());

        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(rand(2, 5)),
            'status' => $status,
            'rating' => $status === WatchingStatus::Watched ? fake()->optional(0.8)->numberBetween(1, 10) : null,
            'year' => fake()->optional(0.9)->numberBetween(1980, 2026),
            'episodes_total' => fake()->optional(0.7)->numberBetween(1, 500),
            'episodes_watched' => fake()->optional(0.6)->numberBetween(0, 100),
            'mal_id' => fake()->boolean(50) ? fake()->unique()->numberBetween(1, 99999) : null,
            'poster_url' => null,
            'description' => fake()->optional(0.6)->paragraphs(2, true),
            'genres' => fake()->optional(0.5)->randomElement(['Action, Adventure', 'Comedy, Slice of Life', 'Drama, Romance']),
            'studios' => fake()->optional(0.5)->randomElement(['MAPPA', 'Bones', 'ufotable', 'Madhouse']),
            'media_type' => fake()->optional(0.7)->randomElement(['TV', 'Movie', 'OVA', 'ONA']),
            'date_added' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function watchlist(): static
    {
        return $this->state(fn () => ['status' => WatchingStatus::Watchlist, 'rating' => null]);
    }

    public function watching(): static
    {
        return $this->state(fn () => ['status' => WatchingStatus::Watching, 'rating' => null]);
    }

    public function watched(): static
    {
        return $this->state(fn () => [
            'status' => WatchingStatus::Watched,
            'rating' => fake()->numberBetween(1, 10),
            'date_finished' => fake()->dateTimeBetween('-6 months', 'now'),
        ]);
    }
}
