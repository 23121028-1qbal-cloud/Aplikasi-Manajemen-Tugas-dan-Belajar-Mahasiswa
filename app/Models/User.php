<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $primaryKey = 'id_user';

    protected $fillable = [
        'username',
        'password',
        'role',
        'is_active',
        'must_change_password',
        'temporary_password_expires_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => Role::class,
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
            'temporary_password_expires_at' => 'datetime',
        ];
    }

    public function mahasiswa(): HasOne
    {
        return $this->hasOne(Mahasiswa::class, 'id_user', 'id_user');
    }

    public function guruDosen(): HasOne
    {
        return $this->hasOne(GuruDosen::class, 'id_user', 'id_user');
    }

    public function notifikasi(): HasMany
    {
        return $this->hasMany(Notifikasi::class, 'id_user', 'id_user');
    }

    public function hasRole(Role|string ...$roles): bool
    {
        $allowed = array_map(
            fn (Role|string $role) => $role instanceof Role ? $role->value : $role,
            $roles
        );

        return in_array($this->role?->value, $allowed, true);
    }
}
