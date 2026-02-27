<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Comic;
use App\Models\User;

class ComicPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Comic $comic): bool
    {
        return $user->id === $comic->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Comic $comic): bool
    {
        return $user->id === $comic->user_id;
    }

    public function delete(User $user, Comic $comic): bool
    {
        return $user->id === $comic->user_id;
    }
}
