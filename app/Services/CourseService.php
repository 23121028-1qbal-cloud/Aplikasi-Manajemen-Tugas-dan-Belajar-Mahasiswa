<?php

namespace App\Services;

use App\Models\MataKuliah;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CourseService
{
    /**
     * @param  array{kode_mata_kuliah?: string, nama_mata_kuliah: string, guru_dosen_id?: int, row_version: int}  $data
     *
     * @throws ValidationException
     */
    public function update(MataKuliah $course, array $data): MataKuliah
    {
        return DB::transaction(function () use ($course, $data) {
            $locked = MataKuliah::query()
                ->whereKey($course->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->row_version !== (int) $data['row_version']) {
                throw ValidationException::withMessages([
                    'row_version' => 'Data mata kuliah telah diubah pada sesi lain. Muat ulang halaman sebelum menyimpan kembali.',
                ]);
            }

            $attributes = [
                'nama_mata_kuliah' => $data['nama_mata_kuliah'],
                'row_version' => $locked->row_version + 1,
            ];

            if (array_key_exists('kode_mata_kuliah', $data)) {
                $attributes['kode_mata_kuliah'] = $data['kode_mata_kuliah'];
            }

            if (array_key_exists('guru_dosen_id', $data)) {
                $attributes['guru_dosen_id'] = $data['guru_dosen_id'];
            }

            $locked->forceFill($attributes)->save();

            return $locked->fresh();
        });
    }
}
