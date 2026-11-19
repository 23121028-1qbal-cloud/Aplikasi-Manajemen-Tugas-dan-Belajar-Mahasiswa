<?php

namespace App\Policies;

use App\Models\ProgresTugasAkademik;
use App\Models\User;

class ProgresTugasAkademikPolicy
{
    public function view(User $user, ProgresTugasAkademik $progress): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('guru_dosen')) {
            return $this->teachesProgressCourse($user, $progress);
        }

        return $this->ownsProgress($user, $progress);
    }

    public function update(User $user, ProgresTugasAkademik $progress): bool
    {
        $task = $progress->tugas;

        if ($task === null || $task->is_archived) {
            return false;
        }

        if ($user->hasRole('guru_dosen')) {
            return $this->teachesProgressCourse($user, $progress);
        }

        return ! $task->is_closed
            && $this->ownsProgress($user, $progress);
    }

    public function correct(User $user, ProgresTugasAkademik $progress): bool
    {
        $task = $progress->tugas;

        return $task !== null
            && ! $task->is_archived
            && $user->hasRole('guru_dosen')
            && $this->teachesProgressCourse($user, $progress);
    }

    private function ownsProgress(User $user, ProgresTugasAkademik $progress): bool
    {
        return $user->hasRole('mahasiswa')
            && $user->mahasiswa !== null
            && $progress->id_mahasiswa === $user->mahasiswa->id_mahasiswa;
    }

    private function teachesProgressCourse(User $user, ProgresTugasAkademik $progress): bool
    {
        $lecturer = $user->guruDosen;
        $course = $progress->tugas?->mataKuliah;

        return $lecturer !== null
            && $course !== null
            && $course->guru_dosen_id === $lecturer->id_guru_dosen;
    }
}
