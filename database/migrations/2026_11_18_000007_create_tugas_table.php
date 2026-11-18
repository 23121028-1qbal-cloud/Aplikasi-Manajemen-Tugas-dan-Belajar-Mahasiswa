<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tugas', function (Blueprint $table) {
            $table->bigIncrements('id_tugas');
            $table->unsignedBigInteger('id_mata_kuliah')->nullable();
            $table->unsignedBigInteger('id_prioritas');
            $table->unsignedBigInteger('id_status')->nullable();
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_pembuat_guru_dosen')->nullable();
            $table->unsignedBigInteger('id_mahasiswa_pemilik')->nullable();
            $table->enum('task_type', ['personal', 'academic']);
            $table->string('judul_tugas', 150);
            $table->text('deskripsi')->nullable();
            $table->dateTime('deadline');
            $table->boolean('is_closed')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->timestamp('tanggal_arsip')->nullable();
            $table->timestamps();

            $table->foreign('id_mata_kuliah')
                ->references('id_mata_kuliah')
                ->on('mata_kuliah')
                ->restrictOnDelete();

            $table->foreign('id_prioritas')
                ->references('id_prioritas')
                ->on('prioritas')
                ->restrictOnDelete();

            $table->foreign('id_status')
                ->references('id_status')
                ->on('status_tugas')
                ->restrictOnDelete();

            $table->foreign('id_user')
                ->references('id_user')
                ->on('users')
                ->restrictOnDelete();

            $table->foreign('id_pembuat_guru_dosen')
                ->references('id_guru_dosen')
                ->on('guru_dosen')
                ->restrictOnDelete();

            $table->foreign('id_mahasiswa_pemilik')
                ->references('id_mahasiswa')
                ->on('mahasiswa')
                ->restrictOnDelete();

            $table->index(['id_mahasiswa_pemilik', 'task_type', 'is_archived'], 'tugas_pemilik_archived_index');
            $table->index(['id_pembuat_guru_dosen', 'task_type', 'is_closed', 'is_archived'], 'tugas_pembuat_index');
            $table->index(['id_mata_kuliah', 'task_type', 'is_closed', 'is_archived'], 'tugas_mata_kuliah_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tugas');
    }
};
