<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AktivitasBelajar extends Model
{
    use HasFactory;

    protected $table = 'aktivitas_belajar';

    protected $primaryKey = 'id_aktivitas';

    protected $fillable = [
        'id_mata_kuliah',
        'id_status_belajar',
        'id_mahasiswa',
        'judul_aktivitas',
        'materi_belajar',
        'waktu_mulai',
        'durasi_menit',
        'tanggal_dibuat',
        'is_archived',
        'tanggal_arsip',
    ];

    protected function casts(): array
    {
        return [
            'waktu_mulai' => 'datetime',
            'tanggal_dibuat' => 'date',
            'durasi_menit' => 'integer',
            'is_archived' => 'boolean',
            'tanggal_arsip' => 'datetime',
            'row_version' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_archived', false);
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa', 'id_mahasiswa');
    }

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class, 'id_mata_kuliah', 'id_mata_kuliah');
    }

    public function statusBelajar(): BelongsTo
    {
        return $this->belongsTo(StatusBelajar::class, 'id_status_belajar', 'id_status_belajar');
    }
}
