<?php

namespace App\Policies;

use App\Enums\EnrollmentStatus;
use App\Models\MataKuliah;
use App\Models\User;

class MataKuliahPolicy
{
    public function view(User $user, MataKuliah $course): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('guru_dosen')) {
            return $this->isAssigned($user, $course);
        }

        return $user->hasRole('mahasiswa')
            && $user->mahasiswa !== null
            && $course->enrollments()
                ->where('id_mahasiswa', $user->mahasiswa->id_mahasiswa)
                ->where('status', EnrollmentStatus::Terdaftar)
                ->exists();
    }

    public function update(User $user, MataKuliah $course): bool
    {
        return $user->hasRole('admin') || $this->isAssigned($user, $course);
    }

    public function manage(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function archive(User $user, MataKuliah $course): bool
    {
        return $user->hasRole('admin');
    }

    private function isAssigned(User $user, MataKuliah $course): bool
    {
        return $user->guruDosen !== null
            && $course->guru_dosen_id === $user->guruDosen->id_guru_dosen;
    }
}
