<?php

namespace Database\Factories;

use App\Enums\EnrollmentStatus;
use App\Enums\Semester;
use App\Models\Enrollment;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Enrollment>
 */
class EnrollmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_mahasiswa' => Mahasiswa::factory(),
            'id_mata_kuliah' => MataKuliah::factory(),
            'tahun_akademik' => '2026/2027',
            'semester' => Semester::Ganjil,
            'tanggal_daftar' => now()->toDateString(),
            'status' => EnrollmentStatus::Terdaftar,
        ];
    }
}
