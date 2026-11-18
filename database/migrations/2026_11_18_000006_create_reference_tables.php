<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prioritas', function (Blueprint $table) {
            $table->bigIncrements('id_prioritas');
            $table->string('nama_prioritas', 20)->unique();
            $table->timestamps();
        });

        Schema::create('status_tugas', function (Blueprint $table) {
            $table->bigIncrements('id_status');
            $table->string('nama_status', 30)->unique();
            $table->timestamps();
        });

        Schema::create('status_belajar', function (Blueprint $table) {
            $table->bigIncrements('id_status_belajar');
            $table->string('nama_status', 30)->unique();
            $table->timestamps();
        });

        $now = now();

        DB::table('prioritas')->insert([
            ['nama_prioritas' => 'Rendah', 'created_at' => $now, 'updated_at' => $now],
            ['nama_prioritas' => 'Sedang', 'created_at' => $now, 'updated_at' => $now],
            ['nama_prioritas' => 'Tinggi', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('status_tugas')->insert([
            ['nama_status' => 'Belum Dikerjakan', 'created_at' => $now, 'updated_at' => $now],
            ['nama_status' => 'Sedang Dikerjakan', 'created_at' => $now, 'updated_at' => $now],
            ['nama_status' => 'Selesai', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('status_belajar')->insert([
            ['nama_status' => 'Direncanakan', 'created_at' => $now, 'updated_at' => $now],
            ['nama_status' => 'Sedang Berlangsung', 'created_at' => $now, 'updated_at' => $now],
            ['nama_status' => 'Selesai', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('status_belajar');
        Schema::dropIfExists('status_tugas');
        Schema::dropIfExists('prioritas');
    }
};
