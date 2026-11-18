<?php

namespace App\Services;

use App\Enums\TaskType;
use App\Models\Mahasiswa;
use App\Models\Tugas;
use Illuminate\Support\Arr;

class PersonalTaskService
{
    private const EDITABLE = [
        'id_mata_kuliah',
        'id_prioritas',
        'id_status',
        'judul_tugas',
        'deskripsi',
        'deadline',
    ];

    public function create(Mahasiswa $student, array $data): Tugas
    {
        return Tugas::create([
            ...Arr::only($data, self::EDITABLE),
            'id_user' => $student->id_user,
            'id_mahasiswa_pemilik' => $student->id_mahasiswa,
            'task_type' => TaskType::Personal,
            'is_closed' => false,
            'is_archived' => false,
        ]);
    }

    public function update(Tugas $task, array $data): Tugas
    {
        $task->update(Arr::only($data, self::EDITABLE));

        return $task->fresh();
    }

    public function archive(Tugas $task): Tugas
    {
        if (! $task->is_archived) {
            $task->update([
                'is_archived' => true,
                'tanggal_arsip' => now(),
            ]);
        }

        return $task->fresh();
    }
}
