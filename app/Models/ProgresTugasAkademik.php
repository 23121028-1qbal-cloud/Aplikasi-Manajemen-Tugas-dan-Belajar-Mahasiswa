<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgresTugasAkademik extends Model
{
    use HasFactory;

    protected $table = 'tugas_akademik_progress';

    protected $primaryKey = 'id_progress';

    protected $fillable = [
        'id_tugas',
        'id_mahasiswa',
        'id_status',
        'id_user_diubah_oleh',
        'tanggal_ubah',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_ubah' => 'datetime',
            'row_version' => 'integer',
        ];
    }

    public function scopeForStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('id_mahasiswa', $studentId);
    }

    public function tugas(): BelongsTo
    {
        return $this->belongsTo(Tugas::class, 'id_tugas', 'id_tugas');
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa', 'id_mahasiswa');
    }

    public function statusTugas(): BelongsTo
    {
        return $this->belongsTo(StatusTugas::class, 'id_status', 'id_status');
    }

    public function diubahOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user_diubah_oleh', 'id_user');
    }
}
