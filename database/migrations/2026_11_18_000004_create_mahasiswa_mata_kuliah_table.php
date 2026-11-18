<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mahasiswa_mata_kuliah', function (Blueprint $table) {
            $table->bigIncrements('id_mahasiswa_mata_kuliah');
            $table->unsignedBigInteger('id_mahasiswa');
            $table->unsignedBigInteger('id_mata_kuliah');
            $table->string('tahun_akademik', 9);
            $table->enum('semester', ['Ganjil', 'Genap']);
            $table->date('tanggal_daftar');
            $table->enum('status', ['Terdaftar', 'Ditolak', 'Dibatalkan', 'Selesai'])->default('Terdaftar');
            $table->timestamps();

            $table->foreign('id_mahasiswa')
                ->references('id_mahasiswa')
                ->on('mahasiswa')
                ->restrictOnDelete();

            $table->foreign('id_mata_kuliah')
                ->references('id_mata_kuliah')
                ->on('mata_kuliah')
                ->restrictOnDelete();

            $table->unique(
                ['id_mahasiswa', 'id_mata_kuliah', 'tahun_akademik', 'semester'],
                'mahasiswa_mata_kuliah_periode_unique'
            );

            $table->index(['id_mahasiswa', 'status']);
            $table->index(['id_mata_kuliah', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mahasiswa_mata_kuliah');
    }
};
