<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class TemporaryPasswordService
{
    public const TTL_HOURS = 72;

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
     * Terbitkan temporary password baru dan paksa penggantian pada login pertama.
     * Nilai plaintext dikembalikan sekali untuk ditampilkan Admin.
     */
    public function issueFor(User $user): string
    {
        $temporaryPassword = $this->generate();

        $user->forceFill([
            'password' => $temporaryPassword,
            'must_change_password' => true,
            'temporary_password_expires_at' => now()->addHours(self::TTL_HOURS),
        ])->save();

        return $temporaryPassword;
    }

    public function hasExpired(User $user): bool
    {
        return $user->temporary_password_expires_at !== null
            && $user->temporary_password_expires_at->isPast();
    }
}
