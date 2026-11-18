<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\GuruDosen;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GuruDosen>
 */
class GuruDosenFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_user' => User::factory()->role(Role::GuruDosen),
            'nip_guru' => fake()->unique()->numerify('19##########'),
            'nama_lengkap' => fake()->name(),
            'is_archived' => false,
            'tanggal_arsip' => null,
        ];
    }
}
