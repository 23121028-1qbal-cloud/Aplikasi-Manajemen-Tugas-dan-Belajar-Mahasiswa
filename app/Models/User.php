<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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

    public function hasRole(Role|string ...$roles): bool
    {
        $allowed = array_map(
            fn (Role|string $role) => $role instanceof Role ? $role->value : $role,
            $roles
        );

        return in_array($this->role?->value, $allowed, true);
    }
}
