<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Movie;
use App\Models\User;

class MoviePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Movie $movie): bool
    {
        return $user->id === $movie->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Movie $movie): bool
    {
        return $user->id === $movie->user_id;
    }

    public function delete(User $user, Movie $movie): bool
    {
        return $user->id === $movie->user_id;
    }
}
