<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateAdminAccount extends Command
{
    protected $signature = 'app:create-admin
        {username : Username akun admin}
        {--password= : Password eksplisit; hindari penggunaan pada shell produksi}';

    protected $description = 'Membuat akun admin awal melalui proses deployment yang terkendali';

    public function handle(): int
    {
        $username = trim((string) $this->argument('username'));

        $validator = Validator::make(
            ['username' => $username],
            ['username' => ['required', 'string', 'max:50', 'unique:users,username']]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        $password = (string) ($this->option('password') ?? '');

        if ($password === '') {
            $password = (string) $this->secret('Password admin (minimal 8 karakter)');
        }

        if (mb_strlen($password) < 8) {
            $this->components->error('Password minimal 8 karakter.');

            return self::FAILURE;
        }

        User::create([
            'username' => $username,
            'password' => $password,
            'role' => Role::Admin,
            'is_active' => true,
        ]);

        $this->components->info("Akun admin '{$username}' berhasil dibuat.");

        return self::SUCCESS;
    }
}
