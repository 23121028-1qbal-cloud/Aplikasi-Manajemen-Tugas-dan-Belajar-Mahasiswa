<?php

namespace App\Services;

use App\Models\AktivitasBelajar;
use App\Models\Mahasiswa;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudyActivityService
{
    private const EDITABLE = [
        'id_mata_kuliah',
        'id_status_belajar',
        'judul_aktivitas',
        'materi_belajar',
        'waktu_mulai',
        'durasi_menit',
    ];

    public function create(Mahasiswa $student, array $data): AktivitasBelajar
    {
        return AktivitasBelajar::create([
            ...Arr::only($data, self::EDITABLE),
            'id_mahasiswa' => $student->id_mahasiswa,
            'tanggal_dibuat' => now()->toDateString(),
            'is_archived' => false,
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function update(AktivitasBelajar $activity, array $data): AktivitasBelajar
    {
        return DB::transaction(function () use ($activity, $data) {
            $locked = AktivitasBelajar::query()
                ->whereKey($activity->getKey())
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

    public function archive(AktivitasBelajar $activity): AktivitasBelajar
    {
        if (! $activity->is_archived) {
            $activity->update([
                'is_archived' => true,
                'tanggal_arsip' => now(),
            ]);
        }

        return $activity->fresh();
    }
}
