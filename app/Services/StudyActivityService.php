<?php

namespace App\Services;

use App\Models\AktivitasBelajar;
use App\Models\Mahasiswa;
use Illuminate\Support\Arr;

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

    public function update(AktivitasBelajar $activity, array $data): AktivitasBelajar
    {
        $activity->update(Arr::only($data, self::EDITABLE));

        return $activity->fresh();
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
