<?php

namespace App\Models;

use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notifikasi extends Model
{
    protected $table = 'notifikasi';

    protected $primaryKey = 'id_notifikasi';

    protected $fillable = [
        'id_user',
        'tipe',
        'judul',
        'pesan',
        'resource_type',
        'resource_id',
        'is_read',
        'read_at',
        'dedup_key',
        'tanggal_dibuat',
    ];

    protected function casts(): array
    {
        return [
            'tipe' => NotificationType::class,
            'is_read' => 'boolean',
            'read_at' => 'datetime',
            'tanggal_dibuat' => 'datetime',
        ];
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
