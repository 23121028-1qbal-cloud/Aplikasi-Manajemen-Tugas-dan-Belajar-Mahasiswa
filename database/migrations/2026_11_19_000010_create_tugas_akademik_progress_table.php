<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tugas_akademik_progress', function (Blueprint $table) {
            $table->bigIncrements('id_progress');
            $table->unsignedBigInteger('id_tugas');
            $table->unsignedBigInteger('id_mahasiswa');
            $table->unsignedBigInteger('id_status');
            $table->unsignedBigInteger('id_user_diubah_oleh');
            $table->dateTime('tanggal_ubah');
            $table->unsignedInteger('row_version')->default(1);
            $table->timestamps();

            $table->foreign('id_tugas')
                ->references('id_tugas')
                ->on('tugas')
                ->restrictOnDelete();

            $table->foreign('id_mahasiswa')
                ->references('id_mahasiswa')
                ->on('mahasiswa')
                ->restrictOnDelete();

            $table->foreign('id_status')
                ->references('id_status')
                ->on('status_tugas')
                ->restrictOnDelete();

            $table->foreign('id_user_diubah_oleh')
                ->references('id_user')
                ->on('users')
                ->restrictOnDelete();

            $table->unique(['id_tugas', 'id_mahasiswa'], 'tugas_akademik_progress_unique');
            $table->index(['id_mahasiswa', 'id_status']);
            $table->index(['id_tugas', 'id_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tugas_akademik_progress');
    }
};
