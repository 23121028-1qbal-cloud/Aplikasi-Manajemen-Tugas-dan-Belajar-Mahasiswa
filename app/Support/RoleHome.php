<?php

namespace App\Support;

use App\Enums\Role;

class RoleHome
{
    public static function routeName(Role $role): string
    {
        return match ($role) {
            Role::Mahasiswa => 'mahasiswa.dashboard',
            Role::GuruDosen => 'lecturer.dashboard',
            Role::Admin => 'admin.dashboard',
        };
    }
}
