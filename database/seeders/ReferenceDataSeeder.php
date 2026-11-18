<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        foreach (['Rendah', 'Sedang', 'Tinggi'] as $nama) {
            DB::table('prioritas')->updateOrInsert(
                ['nama_prioritas' => $nama],
                ['updated_at' => $now, 'created_at' => $now]
            );
        }

        foreach (['Belum Dikerjakan', 'Sedang Dikerjakan', 'Selesai'] as $nama) {
            DB::table('status_tugas')->updateOrInsert(
                ['nama_status' => $nama],
                ['updated_at' => $now, 'created_at' => $now]
            );
        }

        foreach (['Direncanakan', 'Sedang Berlangsung', 'Selesai'] as $nama) {
            DB::table('status_belajar')->updateOrInsert(
                ['nama_status' => $nama],
                ['updated_at' => $now, 'created_at' => $now]
            );
        }
    }
}
