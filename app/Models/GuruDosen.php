<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GuruDosen extends Model
{
    use HasFactory;

    protected $table = 'guru_dosen';

    protected $primaryKey = 'id_guru_dosen';

    protected $fillable = [
        'id_user',
        'nip_guru',
        'nama_lengkap',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function mataKuliah(): HasMany
    {
        return $this->hasMany(MataKuliah::class, 'guru_dosen_id', 'id_guru_dosen');
    }
}
