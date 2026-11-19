<?php

namespace App\Policies;

use App\Enums\TaskType;
use App\Models\Tugas;
use App\Models\User;

class TugasPolicy
{
    public function viewAnyPersonal(User $user): bool
    {
        return $user->hasRole('mahasiswa') && $user->mahasiswa !== null;
    }

    public function createPersonal(User $user): bool
    {
        return $this->viewAnyPersonal($user);
    }

    public function viewPersonal(User $user, Tugas $task): bool
    {
        return $task->task_type === TaskType::Personal
            && $this->ownsPersonalTask($user, $task);
    }

    public function updatePersonal(User $user, Tugas $task): bool
    {
        return ! $task->is_archived
            && $this->viewPersonal($user, $task);
    }

    public function archivePersonal(User $user, Tugas $task): bool
    {
        return $this->viewPersonal($user, $task);
    }

    public function viewAnyAcademic(User $user): bool
    {
        return $user->hasRole('guru_dosen') && $user->guruDosen !== null;
    }

    public function createAcademic(User $user): bool
    {
        return $this->viewAnyAcademic($user);
    }

    public function viewAcademic(User $user, Tugas $task): bool
    {
        if ($task->task_type !== TaskType::Academic) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('guru_dosen')) {
            return $this->teachesTaskCourse($user, $task);
        }

        if ($user->hasRole('mahasiswa') && $user->mahasiswa !== null) {
            return $task->progres()
                ->where('id_mahasiswa', $user->mahasiswa->id_mahasiswa)
                ->exists();
        }

        return false;
    }

    public function manageAcademic(User $user, Tugas $task): bool
    {
        return $task->task_type === TaskType::Academic
            && ! $task->is_archived
            && $user->hasRole('guru_dosen')
            && $this->teachesTaskCourse($user, $task);
    }

    public function monitor(User $user): bool
    {
        return $user->hasRole('admin');
    }

    private function ownsPersonalTask(User $user, Tugas $task): bool
    {
        return $user->hasRole('mahasiswa')
            && $user->mahasiswa !== null
            && $task->id_mahasiswa_pemilik === $user->mahasiswa->id_mahasiswa;
    }

    private function teachesTaskCourse(User $user, Tugas $task): bool
    {
        $lecturer = $user->guruDosen;

        return $lecturer !== null
            && $task->mataKuliah !== null
            && $task->mataKuliah->guru_dosen_id === $lecturer->id_guru_dosen;
    }
}
