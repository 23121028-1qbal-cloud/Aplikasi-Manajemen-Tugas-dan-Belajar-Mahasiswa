<?php

namespace App\Enums;

enum Role: string
{
    case Mahasiswa = 'mahasiswa';
    case GuruDosen = 'guru_dosen';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Mahasiswa => 'Mahasiswa',
            self::GuruDosen => 'Guru/Dosen',
            self::Admin => 'Admin',
        };
    }
}
