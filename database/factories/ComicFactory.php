<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReadingStatus;
use App\Models\Comic;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comic>
 */
class ComicFactory extends Factory
{
    protected $model = Comic::class;

    public function definition(): array
    {
        $status = fake()->randomElement(ReadingStatus::cases());

        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(rand(2, 5)),
            'publisher' => fake()->optional(0.6)->company(),
            'start_year' => fake()->optional(0.7)->numberBetween(1960, 2026),
            'issue_count' => fake()->optional(0.5)->numberBetween(1, 200),
            'status' => $status,
            'rating' => $status === ReadingStatus::Read ? fake()->optional(0.8)->numberBetween(1, 5) : null,
            'description' => fake()->optional(0.5)->paragraphs(2, true),
            'cover_url' => null,
            'date_added' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function wantToRead(): static
    {
        return $this->state(fn () => ['status' => ReadingStatus::WantToRead, 'rating' => null]);
    }

    public function reading(): static
    {
        return $this->state(fn () => ['status' => ReadingStatus::Reading, 'rating' => null]);
    }

    public function read(): static
    {
        return $this->state(fn () => [
            'status' => ReadingStatus::Read,
            'rating' => fake()->numberBetween(1, 5),
            'date_finished' => fake()->dateTimeBetween('-6 months', 'now'),
        ]);
    }
}
