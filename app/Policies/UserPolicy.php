<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function view(User $user, User $account): bool
    {
        return $user->id_user === $account->id_user;
    }

    public function updatePassword(User $user, User $account): bool
    {
        return $user->id_user === $account->id_user;
    }
}
