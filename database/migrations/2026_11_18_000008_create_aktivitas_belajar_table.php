<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aktivitas_belajar', function (Blueprint $table) {
            $table->bigIncrements('id_aktivitas');
            $table->unsignedBigInteger('id_mata_kuliah')->nullable();
            $table->unsignedBigInteger('id_status_belajar');
            $table->unsignedBigInteger('id_mahasiswa');
            $table->string('judul_aktivitas', 150);
            $table->text('materi_belajar')->nullable();
            $table->dateTime('waktu_mulai');
            $table->unsignedSmallInteger('durasi_menit');
            $table->date('tanggal_dibuat');
            $table->boolean('is_archived')->default(false);
            $table->timestamp('tanggal_arsip')->nullable();
            $table->timestamps();

            $table->foreign('id_mata_kuliah')
                ->references('id_mata_kuliah')
                ->on('mata_kuliah')
                ->restrictOnDelete();

            $table->foreign('id_status_belajar')
                ->references('id_status_belajar')
                ->on('status_belajar')
                ->restrictOnDelete();

            $table->foreign('id_mahasiswa')
                ->references('id_mahasiswa')
                ->on('mahasiswa')
                ->restrictOnDelete();

            $table->index(['id_mahasiswa', 'is_archived', 'waktu_mulai'], 'aktivitas_belajar_owner_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aktivitas_belajar');
    }
};
