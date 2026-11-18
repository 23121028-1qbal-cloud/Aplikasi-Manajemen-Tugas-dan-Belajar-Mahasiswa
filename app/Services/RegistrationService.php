<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RegistrationService
{
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'username' => $data['username'],
                'password' => $data['password'],
                'role' => Role::Mahasiswa,
                'is_active' => true,
            ]);

            $user->mahasiswa()->create([
                'npm' => $data['npm'],
                'nama_lengkap' => $data['nama_lengkap'],
            ]);

            return $user->load('mahasiswa');
        });
    }
}
