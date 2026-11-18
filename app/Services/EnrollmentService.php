<?php

namespace App\Services;

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\MataKuliah;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EnrollmentService
{
    public function __construct(private readonly AcademicTaskService $academicTasks) {}

    /**
     * Membuat enrollment baru atau mengaktifkan kembali baris periode yang sama.
     * `tanggal_daftar` tidak ditimpa pada reaktivasi (REC-05).
     *
     * @return array{enrollment: Enrollment, reactivated: bool}
     *
     * @throws ValidationException
     */
    public function enroll(array $data): array
    {
        return DB::transaction(function () use ($data) {
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
                    'id_mata_kuliah' => 'Mata kuliah yang diarsipkan tidak dapat menerima enrollment baru.',
                ]);
            }

            $existing = Enrollment::query()
                ->where('id_mahasiswa', $data['id_mahasiswa'])
                ->where('id_mata_kuliah', $data['id_mata_kuliah'])
                ->where('tahun_akademik', $data['tahun_akademik'])
                ->where('semester', $data['semester'])
                ->lockForUpdate()
                ->first();

            if ($existing !== null) {
                if ($existing->status === EnrollmentStatus::Terdaftar) {
                    throw ValidationException::withMessages([
                        'id_mata_kuliah' => 'Mahasiswa sudah terdaftar pada mata kuliah dan periode ini.',
                    ]);
                }

                $existing->update(['status' => EnrollmentStatus::Terdaftar]);

                $fresh = $existing->fresh();
                $this->academicTasks->distributeForEnrollment($fresh);

                return [
                    'enrollment' => $fresh,
                    'reactivated' => true,
                ];
            }

            try {
                $enrollment = Enrollment::create([
                    'id_mahasiswa' => $data['id_mahasiswa'],
                    'id_mata_kuliah' => $data['id_mata_kuliah'],
                    'tahun_akademik' => $data['tahun_akademik'],
                    'semester' => $data['semester'],
                    'tanggal_daftar' => now()->toDateString(),
                    'status' => EnrollmentStatus::Terdaftar,
                ]);
            } catch (UniqueConstraintViolationException) {
                // Backstop untuk race condition: unique composite menolak duplikasi.
                throw ValidationException::withMessages([
                    'id_mata_kuliah' => 'Enrollment untuk periode ini sudah ada.',
                ]);
            }

            $this->academicTasks->distributeForEnrollment($enrollment);

            return [
                'enrollment' => $enrollment,
                'reactivated' => false,
            ];
        });
    }

    /**
     * @throws ValidationException
     */
    public function changeStatus(Enrollment $enrollment, EnrollmentStatus $status): Enrollment
    {
        return DB::transaction(function () use ($enrollment, $status) {
            $locked = Enrollment::query()
                ->whereKey($enrollment->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $locked->status->canTransitionTo($status)) {
                throw ValidationException::withMessages([
                    'status' => 'Transisi status enrollment tidak diizinkan.',
                ]);
            }

            $locked->update(['status' => $status]);

            return $locked->fresh();
        });
    }
}
