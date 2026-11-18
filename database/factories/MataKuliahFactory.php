<?php

namespace Database\Factories;

use App\Models\GuruDosen;
use App\Models\MataKuliah;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MataKuliah>
 */
class MataKuliahFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kode_mata_kuliah' => 'MK'.fake()->unique()->numerify('####'),
            'nama_mata_kuliah' => 'Mata Kuliah '.fake()->unique()->numerify('##'),
            'guru_dosen_id' => GuruDosen::factory(),
            'is_archived' => false,
            'tanggal_arsip' => null,
            'row_version' => 1,
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
