<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Shelf;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Shelf>
 */
class ShelfFactory extends Factory
{
    protected $model = Shelf::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(random_int(1, 3), true);

        return [
            'user_id' => User::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
