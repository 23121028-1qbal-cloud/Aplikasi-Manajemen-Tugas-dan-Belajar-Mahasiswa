<?php

namespace App\Models;

use App\Enums\EnrollmentStatus;
use App\Enums\Semester;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa_mata_kuliah';

    protected $primaryKey = 'id_mahasiswa_mata_kuliah';

    protected $fillable = [
        'id_mahasiswa',
        'id_mata_kuliah',
        'tahun_akademik',
        'semester',
        'tanggal_daftar',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'semester' => Semester::class,
            'status' => EnrollmentStatus::class,
            'tanggal_daftar' => 'date',
        ];
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa', 'id_mahasiswa');
    }

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class, 'id_mata_kuliah', 'id_mata_kuliah');
    }
}
