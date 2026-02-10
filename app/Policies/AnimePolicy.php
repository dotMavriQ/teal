<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Anime;
use App\Models\User;

class AnimePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Anime $anime): bool
    {
        return $user->id === $anime->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Anime $anime): bool
    {
        return $user->id === $anime->user_id;
    }

    public function delete(User $user, Anime $anime): bool
    {
        return $user->id === $anime->user_id;
    }
}
