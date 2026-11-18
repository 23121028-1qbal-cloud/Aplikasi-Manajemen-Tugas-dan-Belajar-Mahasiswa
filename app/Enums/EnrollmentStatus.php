<?php

namespace App\Enums;

enum EnrollmentStatus: string
{
    case Terdaftar = 'Terdaftar';
    case Ditolak = 'Ditolak';
    case Dibatalkan = 'Dibatalkan';
    case Selesai = 'Selesai';

    /**
     * REC-04: hanya enrollment aktif yang boleh dibatalkan atau diselesaikan.
     * Reaktivasi dilakukan lewat enrollment periode yang sama, bukan transisi langsung.
     */
    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Terdaftar => in_array($target, [self::Dibatalkan, self::Selesai], true),
            default => false,
        };
    }
}
