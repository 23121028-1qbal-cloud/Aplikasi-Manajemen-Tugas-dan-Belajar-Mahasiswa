<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MataKuliah extends Model
{
    use HasFactory;

    protected $table = 'mata_kuliah';

    protected $primaryKey = 'id_mata_kuliah';

    protected $fillable = [
        'kode_mata_kuliah',
        'nama_mata_kuliah',
        'guru_dosen_id',
        'is_archived',
        'tanggal_arsip',
    ];

    protected function casts(): array
    {
        return [
            'is_archived' => 'boolean',
            'tanggal_arsip' => 'datetime',
        ];
    }

    public function guruDosen(): BelongsTo
    {
        return $this->belongsTo(GuruDosen::class, 'guru_dosen_id', 'id_guru_dosen');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'id_mata_kuliah', 'id_mata_kuliah');
    }
}
