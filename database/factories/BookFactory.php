<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReadingStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    public function definition(): array
    {
        $status = fake()->randomElement(ReadingStatus::cases());
        $dateStarted = null;
        $dateFinished = null;

        if ($status === ReadingStatus::Reading) {
            $dateStarted = fake()->dateTimeBetween('-1 month', 'now');
        } elseif ($status === ReadingStatus::Read) {
            $dateStarted = fake()->dateTimeBetween('-1 year', '-1 month');
            $dateFinished = fake()->dateTimeBetween($dateStarted, 'now');
        }

        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(rand(2, 6)),
            'author' => fake()->name(),
            'isbn' => fake()->optional(0.7)->isbn10(),
            'isbn13' => fake()->optional(0.7)->isbn13(),
            'cover_url' => null,
            'description' => fake()->optional(0.8)->paragraphs(2, true),
            'page_count' => fake()->optional(0.9)->numberBetween(100, 800),
            'published_date' => fake()->optional(0.8)->dateTimeBetween('-50 years', 'now'),
            'publisher' => fake()->optional(0.6)->company(),
            'goodreads_id' => null,
            'status' => $status,
            'rating' => $status === ReadingStatus::Read ? fake()->optional(0.8)->numberBetween(1, 5) : null,
            'date_started' => $dateStarted,
            'date_finished' => $dateFinished,
            'notes' => fake()->optional(0.3)->paragraph(),
        ];
    }

    public function wantToRead(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReadingStatus::WantToRead,
            'date_started' => null,
            'date_finished' => null,
            'rating' => null,
        ]);
    }

    public function reading(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReadingStatus::Reading,
            'date_started' => fake()->dateTimeBetween('-1 month', 'now'),
            'date_finished' => null,
            'rating' => null,
        ]);
    }

    public function read(): static
    {
        return $this->state(function (array $attributes) {
            $dateStarted = fake()->dateTimeBetween('-1 year', '-1 month');

            return [
                'status' => ReadingStatus::Read,
                'date_started' => $dateStarted,
                'date_finished' => fake()->dateTimeBetween($dateStarted, 'now'),
                'rating' => fake()->numberBetween(1, 5),
            ];
        });
    }
}
