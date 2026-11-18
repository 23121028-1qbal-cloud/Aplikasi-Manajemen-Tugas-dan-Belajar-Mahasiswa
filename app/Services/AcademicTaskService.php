<?php

namespace App\Services;

use App\Enums\EnrollmentStatus;
use App\Enums\TaskType;
use App\Models\Enrollment;
use App\Models\GuruDosen;
use App\Models\MataKuliah;
use App\Models\ProgresTugasAkademik;
use App\Models\StatusTugas;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AcademicTaskService
{
    private const EDITABLE = [
        'id_prioritas',
        'judul_tugas',
        'deskripsi',
        'deadline',
    ];

    /**
     * @return array{task: Tugas, distributed: int}
     *
     * @throws ValidationException
     */
    public function create(GuruDosen $lecturer, array $data): array
    {
        return DB::transaction(function () use ($lecturer, $data) {
            $course = MataKuliah::query()
                ->whereKey($data['id_mata_kuliah'])
                ->lockForUpdate()
                ->first();

            if ($course === null) {
                throw ValidationException::withMessages([
                    'id_mata_kuliah' => 'Mata kuliah tidak ditemukan.',
                ]);
            }

            if ($course->is_archived) {
                throw ValidationException::withMessages([
                    'id_mata_kuliah' => 'Mata kuliah yang diarsipkan tidak dapat menerima tugas akademik baru.',
                ]);
            }

            if ($course->guru_dosen_id !== $lecturer->id_guru_dosen) {
                throw ValidationException::withMessages([
                    'id_mata_kuliah' => 'Anda hanya dapat membuat tugas untuk mata kuliah yang diampu.',
                ]);
            }

            $task = Tugas::create([
                ...Arr::only($data, self::EDITABLE),
                'id_mata_kuliah' => $course->id_mata_kuliah,
                'id_status' => null,
                'id_user' => $lecturer->id_user,
                'id_pembuat_guru_dosen' => $lecturer->id_guru_dosen,
                'id_mahasiswa_pemilik' => null,
                'task_type' => TaskType::Academic,
                'is_closed' => false,
                'is_archived' => false,
                'tanggal_arsip' => null,
                'row_version' => 1,
            ]);

            $distributed = $this->distributeLocked($task, $lecturer->id_user);

            return [
                'task' => $task->fresh(),
                'distributed' => $distributed,
            ];
        });
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
                    'row_version' => 'Data tugas akademik telah diubah pada sesi lain. Muat ulang halaman sebelum menyimpan kembali.',
                ]);
            }

            $locked->forceFill([
                ...Arr::only($data, self::EDITABLE),
                'row_version' => $locked->row_version + 1,
            ])->save();

            return $locked->fresh();
        });
    }

    public function close(Tugas $task): Tugas
    {
        return DB::transaction(function () use ($task) {
            $locked = Tugas::query()
                ->whereKey($task->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $locked->is_closed) {
                $locked->forceFill([
                    'is_closed' => true,
                    'row_version' => $locked->row_version + 1,
                ])->save();
            }

            return $locked->fresh();
        });
    }

    /**
     * Distribusi untuk enrollment yang baru dibuat/diaktifkan. Hanya enrollment
     * Terdaftar dan tugas terbuka yang menerima progres baru.
     */
    public function distributeForEnrollment(Enrollment $enrollment): int
    {
        return DB::transaction(function () use ($enrollment) {
            $lockedEnrollment = Enrollment::query()
                ->whereKey($enrollment->getKey())
                ->lockForUpdate()
                ->first();

            if ($lockedEnrollment === null || $lockedEnrollment->status !== EnrollmentStatus::Terdaftar) {
                return 0;
            }

            $course = MataKuliah::query()
                ->whereKey($lockedEnrollment->id_mata_kuliah)
                ->lockForUpdate()
                ->first();

            if ($course === null || $course->is_archived) {
                return 0;
            }

            $distributed = 0;

            Tugas::query()
                ->academic()
                ->open()
                ->where('id_mata_kuliah', $course->id_mata_kuliah)
                ->orderBy('id_tugas')
                ->each(function (Tugas $task) use ($lockedEnrollment, &$distributed) {
                    $distributed += $this->insertMissingProgress(
                        $task,
                        [$lockedEnrollment->id_mahasiswa],
                        $task->id_user
                    );
                });

            return $distributed;
        });
    }

    /**
     * @throws ValidationException
     */
    public function updateProgress(
        ProgresTugasAkademik $progress,
        int $statusId,
        User $actor,
        int $rowVersion
    ): ProgresTugasAkademik {
        return DB::transaction(function () use ($progress, $statusId, $actor, $rowVersion) {
            $locked = ProgresTugasAkademik::query()
                ->whereKey($progress->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->row_version !== $rowVersion) {
                throw ValidationException::withMessages([
                    'row_version' => 'Progres telah diubah pada sesi lain. Muat ulang halaman sebelum menyimpan kembali.',
                ]);
            }

            if (! StatusTugas::query()->whereKey($statusId)->exists()) {
                throw ValidationException::withMessages([
                    'id_status' => 'Status tugas tidak valid.',
                ]);
            }

            $locked->forceFill([
                'id_status' => $statusId,
                'id_user_diubah_oleh' => $actor->id_user,
                'tanggal_ubah' => now(),
                'row_version' => $locked->row_version + 1,
            ])->save();

            return $locked->fresh();
        });
    }

    private function distributeLocked(Tugas $task, int $actorUserId): int
    {
        $studentIds = Enrollment::query()
            ->where('id_mata_kuliah', $task->id_mata_kuliah)
            ->where('status', EnrollmentStatus::Terdaftar)
            ->orderBy('id_mahasiswa')
            ->pluck('id_mahasiswa')
            ->all();

        return $this->insertMissingProgress($task, $studentIds, $actorUserId);
    }

    /**
     * Idempotent: unique (id_tugas, id_mahasiswa) dan insertOrIgnore membuat
     * distribusi ulang tidak pernah membuat baris ganda atau mereset progres.
     *
     * @param  array<int, int>  $studentIds
     */
    private function insertMissingProgress(Tugas $task, array $studentIds, int $actorUserId): int
    {
        if ($studentIds === []) {
            return 0;
        }

        $statusId = StatusTugas::query()
            ->where('nama_status', 'Belum Dikerjakan')
            ->value('id_status');

        if ($statusId === null) {
            throw new \RuntimeException('Status tugas awal tidak tersedia.');
        }

        $existing = ProgresTugasAkademik::query()
            ->where('id_tugas', $task->id_tugas)
            ->whereIn('id_mahasiswa', $studentIds)
            ->pluck('id_mahasiswa')
            ->all();

        $missing = array_values(array_diff($studentIds, $existing));

        if ($missing === []) {
            return 0;
        }

        $now = now();

        $rows = array_map(fn (int $studentId) => [
            'id_tugas' => $task->id_tugas,
            'id_mahasiswa' => $studentId,
            'id_status' => $statusId,
            'id_user_diubah_oleh' => $actorUserId,
            'tanggal_ubah' => $now,
            'row_version' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ], $missing);

        return ProgresTugasAkademik::query()->insertOrIgnore($rows);
    }
}
