<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Show;
use App\Models\User;

class ShowPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Show $show): bool
    {
        return $user->id === $show->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Show $show): bool
    {
        return $user->id === $show->user_id;
    }

    public function delete(User $user, Show $show): bool
    {
        return $user->id === $show->user_id;
    }
}
