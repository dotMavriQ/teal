<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CollectionStatus;
use App\Enums\OwnershipStatus;
use App\Models\Album;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlbumFactory extends Factory
{
    protected $model = Album::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'artist' => $this->faker->name(),
            'year' => $this->faker->numberBetween(1960, 2025),
            'status' => CollectionStatus::Wishlist,
            'ownership' => OwnershipStatus::NotOwned,
        ];
    }
}
