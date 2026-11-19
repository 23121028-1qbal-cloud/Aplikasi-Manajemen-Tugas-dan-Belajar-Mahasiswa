<?php

namespace App\Policies;

use App\Models\Enrollment;
use App\Models\User;

class EnrollmentPolicy
{
    public function view(User $user, Enrollment $enrollment): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('mahasiswa')) {
            return $user->mahasiswa !== null
                && $enrollment->id_mahasiswa === $user->mahasiswa->id_mahasiswa;
        }

        if ($user->hasRole('guru_dosen')) {
            return $user->guruDosen !== null
                && $enrollment->mataKuliah !== null
                && $enrollment->mataKuliah->guru_dosen_id === $user->guruDosen->id_guru_dosen;
        }

        return false;
    }

    public function manage(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
