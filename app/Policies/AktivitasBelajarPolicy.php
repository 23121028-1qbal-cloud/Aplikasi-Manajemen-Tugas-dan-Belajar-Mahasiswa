<?php

namespace App\Policies;

use App\Models\AktivitasBelajar;
use App\Models\User;

class AktivitasBelajarPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('mahasiswa') && $user->mahasiswa !== null;
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function view(User $user, AktivitasBelajar $activity): bool
    {
        return $this->ownsActivity($user, $activity);
    }

    public function update(User $user, AktivitasBelajar $activity): bool
    {
        return ! $activity->is_archived && $this->ownsActivity($user, $activity);
    }

    public function archive(User $user, AktivitasBelajar $activity): bool
    {
        return $this->ownsActivity($user, $activity);
    }

    private function ownsActivity(User $user, AktivitasBelajar $activity): bool
    {
        return $user->hasRole('mahasiswa')
            && $user->mahasiswa !== null
            && $activity->id_mahasiswa === $user->mahasiswa->id_mahasiswa;
    }
}
