<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReadingStatus;
use App\Models\Comic;
use App\Models\ComicIssue;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ComicIssue>
 */
class ComicIssueFactory extends Factory
{
    protected $model = ComicIssue::class;

    public function definition(): array
    {
        return [
            'comic_id' => Comic::factory(),
            'user_id' => User::factory(),
            'title' => fake()->optional(0.5)->sentence(3),
            'issue_number' => fake()->numberBetween(1, 100),
            'status' => fake()->randomElement(ReadingStatus::cases()),
            'rating' => fake()->optional(0.3)->numberBetween(1, 5),
        ];
    }
}
