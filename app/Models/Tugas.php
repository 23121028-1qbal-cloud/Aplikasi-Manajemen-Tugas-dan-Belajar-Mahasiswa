<?php

namespace App\Models;

use App\Enums\TaskType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tugas extends Model
{
    use HasFactory;

    protected $table = 'tugas';

    protected $primaryKey = 'id_tugas';

    protected $fillable = [
        'id_mata_kuliah',
        'id_prioritas',
        'id_status',
        'id_user',
        'id_pembuat_guru_dosen',
        'id_mahasiswa_pemilik',
        'task_type',
        'judul_tugas',
        'deskripsi',
        'deadline',
        'is_closed',
        'is_archived',
        'tanggal_arsip',
    ];

    protected function casts(): array
    {
        return [
            'task_type' => TaskType::class,
            'deadline' => 'datetime',
            'is_closed' => 'boolean',
            'is_archived' => 'boolean',
            'tanggal_arsip' => 'datetime',
        ];
    }

    public function scopePersonal(Builder $query): Builder
    {
        return $query->where('task_type', TaskType::Personal->value);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_archived', false);
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa_pemilik', 'id_mahasiswa');
    }

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class, 'id_mata_kuliah', 'id_mata_kuliah');
    }

    public function prioritas(): BelongsTo
    {
        return $this->belongsTo(Prioritas::class, 'id_prioritas', 'id_prioritas');
    }

    public function statusTugas(): BelongsTo
    {
        return $this->belongsTo(StatusTugas::class, 'id_status', 'id_status');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function guruDosenPembuat(): BelongsTo
    {
        return $this->belongsTo(GuruDosen::class, 'id_pembuat_guru_dosen', 'id_guru_dosen');
    }

    public function isCompleted(): bool
    {
        return $this->statusTugas?->nama_status === 'Selesai';
    }
}
