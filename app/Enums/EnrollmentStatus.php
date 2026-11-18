<?php

namespace App\Enums;

enum EnrollmentStatus: string
{
    case Terdaftar = 'Terdaftar';
    case Ditolak = 'Ditolak';
    case Dibatalkan = 'Dibatalkan';
    case Selesai = 'Selesai';
}
