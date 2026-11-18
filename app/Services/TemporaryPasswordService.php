<?php

namespace App\Services;

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
}
