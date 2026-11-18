<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\GuruDosen;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LecturerAccountService
{
    public function __construct(private readonly TemporaryPasswordService $temporaryPasswords) {}

    /**
     * @return array{user: User, lecturer: GuruDosen, temporary_password: string}
     */
    public function create(array $data): array
    {
        $temporaryPassword = $this->temporaryPasswords->generate();

        return DB::transaction(function () use ($data, $temporaryPassword) {
            $user = User::create([
                'username' => $data['username'],
                'password' => $temporaryPassword,
                'role' => Role::GuruDosen,
                'is_active' => true,
            ]);

            $lecturer = $user->guruDosen()->create([
                'nip_guru' => $data['nip_guru'],
                'nama_lengkap' => $data['nama_lengkap'],
            ]);

            return [
                'user' => $user,
                'lecturer' => $lecturer,
                'temporary_password' => $temporaryPassword,
            ];
        });
    }
}
