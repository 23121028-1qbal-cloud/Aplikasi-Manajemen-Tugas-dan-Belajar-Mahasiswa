<?php

namespace Database\Factories;

use App\Enums\TaskType;
use App\Models\Mahasiswa;
use App\Models\Prioritas;
use App\Models\StatusTugas;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tugas>
 */
class TugasFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_mata_kuliah' => null,
            'id_prioritas' => fn () => Prioritas::query()->where('nama_prioritas', 'Sedang')->value('id_prioritas'),
            'id_status' => fn () => StatusTugas::query()->where('nama_status', 'Belum Dikerjakan')->value('id_status'),
            'id_user' => User::factory(),
            'id_pembuat_guru_dosen' => null,
            'id_mahasiswa_pemilik' => Mahasiswa::factory(),
            'task_type' => TaskType::Personal,
            'judul_tugas' => fake()->sentence(4),
            'deskripsi' => fake()->sentence(),
            'deadline' => now()->addDays(7),
            'is_closed' => false,
            'is_archived' => false,
            'tanggal_arsip' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Tugas $tugas) {
            if ($tugas->mahasiswa !== null && $tugas->id_user !== $tugas->mahasiswa->id_user) {
                $tugas->forceFill(['id_user' => $tugas->mahasiswa->id_user])->save();
            }
        });
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'id_status' => StatusTugas::query()->where('nama_status', 'Selesai')->value('id_status'),
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_archived' => true,
            'tanggal_arsip' => now(),
        ]);
    }
}
