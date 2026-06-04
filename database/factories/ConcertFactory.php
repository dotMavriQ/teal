<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ListeningStatus;
use App\Models\Concert;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConcertFactory extends Factory
{
    protected $model = Concert::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'artist' => $this->faker->name(),
            'venue' => $this->faker->company(),
            'city' => $this->faker->city(),
            'event_date' => $this->faker->date(),
            'status' => ListeningStatus::Attended,
        ];
    }
}
