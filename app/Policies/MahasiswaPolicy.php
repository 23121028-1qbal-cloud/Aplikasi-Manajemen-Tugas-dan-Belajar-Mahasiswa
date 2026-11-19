<?php

namespace App\Policies;

use App\Models\Mahasiswa;
use App\Models\User;

class MahasiswaPolicy
{
    public function view(User $user, Mahasiswa $student): bool
    {
        return $user->hasRole('admin')
            || ($user->id_user === $student->id_user);
    }

    public function update(User $user, Mahasiswa $student): bool
    {
        return $user->hasRole('admin');
    }

    public function archive(User $user, Mahasiswa $student): bool
    {
        return $user->hasRole('admin');
    }

    public function restore(User $user, Mahasiswa $student): bool
    {
        return $user->hasRole('admin');
    }

    public function manage(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
