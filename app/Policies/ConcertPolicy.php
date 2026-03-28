<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Concert;
use App\Models\User;

class ConcertPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Concert $concert): bool
    {
        return $user->id === $concert->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Concert $concert): bool
    {
        return $user->id === $concert->user_id;
    }

    public function delete(User $user, Concert $concert): bool
    {
        return $user->id === $concert->user_id;
    }
}
