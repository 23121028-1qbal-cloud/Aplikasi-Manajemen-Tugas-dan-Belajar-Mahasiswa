<?php

namespace App\Policies;

use App\Models\GuruDosen;
use App\Models\User;

class GuruDosenPolicy
{
    public function view(User $user, GuruDosen $lecturer): bool
    {
        return $user->hasRole('admin')
            || ($user->id_user === $lecturer->id_user);
    }

    public function update(User $user, GuruDosen $lecturer): bool
    {
        return $user->hasRole('admin');
    }

    public function archive(User $user, GuruDosen $lecturer): bool
    {
        return $user->hasRole('admin');
    }

    public function restore(User $user, GuruDosen $lecturer): bool
    {
        return $user->hasRole('admin');
    }

    public function manage(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
