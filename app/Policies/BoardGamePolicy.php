<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\BoardGame;
use App\Models\User;

class BoardGamePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, BoardGame $boardGame): bool
    {
        return $user->id === $boardGame->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, BoardGame $boardGame): bool
    {
        return $user->id === $boardGame->user_id;
    }

    public function delete(User $user, BoardGame $boardGame): bool
    {
        return $user->id === $boardGame->user_id;
    }
}
