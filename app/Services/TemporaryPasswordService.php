<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class TemporaryPasswordService
{
    /**
     * Password hanya dikembalikan sekali ke pemanggil untuk ditampilkan.
     * Jangan mencatat nilai ini ke log atau menyimpannya sebagai plaintext.
     */
    public function generate(): string
    {
        return Str::password(12, symbols: false);
    }

    public function isAcceptable(string $password): bool
    {
        return mb_strlen($password) >= 8;
    }

    /**
     * Terbitkan password baru untuk akun yang sudah ada.
     * Nilai plaintext dikembalikan sekali untuk ditampilkan Admin.
     */
    public function resetFor(User $user): string
    {
        $temporaryPassword = $this->generate();

        $user->update(['password' => $temporaryPassword]);

        return $temporaryPassword;
    }
}
