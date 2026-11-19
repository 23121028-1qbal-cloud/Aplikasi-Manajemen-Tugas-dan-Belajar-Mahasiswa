<?php

namespace App\Services;

use App\Models\GuruDosen;
use App\Models\Mahasiswa;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountArchiveService
{
    public function archiveStudent(Mahasiswa $student): Mahasiswa
    {
        return DB::transaction(function () use ($student) {
            $locked = Mahasiswa::query()
                ->whereKey($student->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $locked->is_archived) {
                $locked->forceFill([
                    'is_archived' => true,
                    'tanggal_arsip' => now(),
                ])->save();

                $locked->user?->forceFill(['is_active' => false])->save();
            }

            return $locked->fresh();
        });
    }

    public function restoreStudent(Mahasiswa $student): Mahasiswa
    {
        return DB::transaction(function () use ($student) {
            $locked = Mahasiswa::query()
                ->whereKey($student->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->is_archived) {
                $locked->forceFill([
                    'is_archived' => false,
                    'tanggal_arsip' => null,
                ])->save();

                $locked->user?->forceFill(['is_active' => true])->save();
            }

            return $locked->fresh();
        });
    }

    /**
     * @throws ValidationException
     */
    public function archiveLecturer(GuruDosen $lecturer): GuruDosen
    {
        return DB::transaction(function () use ($lecturer) {
            $locked = GuruDosen::query()
                ->whereKey($lecturer->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->is_archived) {
                return $locked->fresh();
            }

            if ($locked->mataKuliah()->where('is_archived', false)->exists()) {
                throw ValidationException::withMessages([
                    'archive' => 'Guru/Dosen masih memiliki mata kuliah non-arsip. Pindahkan atau arsipkan mata kuliah terlebih dahulu.',
                ]);
            }

            $locked->forceFill([
                'is_archived' => true,
                'tanggal_arsip' => now(),
            ])->save();

            $locked->user?->forceFill(['is_active' => false])->save();

            return $locked->fresh();
        });
    }

    public function restoreLecturer(GuruDosen $lecturer): GuruDosen
    {
        return DB::transaction(function () use ($lecturer) {
            $locked = GuruDosen::query()
                ->whereKey($lecturer->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->is_archived) {
                $locked->forceFill([
                    'is_archived' => false,
                    'tanggal_arsip' => null,
                ])->save();

                $locked->user?->forceFill(['is_active' => true])->save();
            }

            return $locked->fresh();
        });
    }
}
