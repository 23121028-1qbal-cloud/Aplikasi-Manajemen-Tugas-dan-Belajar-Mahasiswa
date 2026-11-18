<?php

namespace Database\Factories;

use App\Models\AktivitasBelajar;
use App\Models\Mahasiswa;
use App\Models\StatusBelajar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AktivitasBelajar>
 */
class AktivitasBelajarFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_mata_kuliah' => null,
            'id_status_belajar' => fn () => StatusBelajar::query()->where('nama_status', 'Direncanakan')->value('id_status_belajar'),
            'id_mahasiswa' => Mahasiswa::factory(),
            'judul_aktivitas' => fake()->sentence(3),
            'materi_belajar' => fake()->sentence(),
            'waktu_mulai' => now()->addDay(),
            'durasi_menit' => 60,
            'tanggal_dibuat' => now()->toDateString(),
            'is_archived' => false,
            'tanggal_arsip' => null,
        ];
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_archived' => true,
            'tanggal_arsip' => now(),
        ]);
    }
}
