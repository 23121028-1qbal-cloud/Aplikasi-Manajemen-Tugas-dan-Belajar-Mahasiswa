<?php

namespace Database\Factories;

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mahasiswa>
 */
class MahasiswaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_user' => User::factory(),
            'npm' => fake()->unique()->numerify('20########'),
            'nama_lengkap' => fake()->name(),
            'is_archived' => false,
            'tanggal_arsip' => null,
        ];
    }
}
