<?php

namespace App\Services;

use App\Enums\TaskType;
use App\Models\Mahasiswa;
use App\Models\Tugas;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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

    /**
     * @throws ValidationException
     */
    public function update(Tugas $task, array $data): Tugas
    {
        return DB::transaction(function () use ($task, $data) {
            $locked = Tugas::query()
                ->whereKey($task->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->row_version !== (int) $data['row_version']) {
                throw ValidationException::withMessages([
                    'row_version' => 'Data telah diubah pada sesi lain. Muat ulang halaman sebelum menyimpan kembali.',
                ]);
            }

            $locked->forceFill([
                ...Arr::only($data, self::EDITABLE),
                'row_version' => $locked->row_version + 1,
            ])->save();

            return $locked->fresh();
        });
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
