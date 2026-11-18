<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mata_kuliah', function (Blueprint $table) {
            $table->bigIncrements('id_mata_kuliah');
            $table->string('kode_mata_kuliah', 20)->unique();
            $table->string('nama_mata_kuliah', 100);
            $table->unsignedBigInteger('guru_dosen_id');
            $table->boolean('is_archived')->default(false);
            $table->timestamp('tanggal_arsip')->nullable();
            $table->timestamps();

            $table->foreign('guru_dosen_id')
                ->references('id_guru_dosen')
                ->on('guru_dosen')
                ->restrictOnDelete();

            $table->index(['guru_dosen_id', 'is_archived']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mata_kuliah');
    }
};
