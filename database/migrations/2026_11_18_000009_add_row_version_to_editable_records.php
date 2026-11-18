<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Concurrency per entitas (TBD-PO-07): hanya record yang diedit lewat form
     * yang memakai row_version. Tidak diterapkan sebagai kolom universal.
     */
    public function up(): void
    {
        Schema::table('tugas', function (Blueprint $table) {
            $table->unsignedInteger('row_version')->default(1);
        });

        Schema::table('aktivitas_belajar', function (Blueprint $table) {
            $table->unsignedInteger('row_version')->default(1);
        });

        Schema::table('mata_kuliah', function (Blueprint $table) {
            $table->unsignedInteger('row_version')->default(1);
        });
    }

    public function down(): void
    {
        Schema::table('mata_kuliah', function (Blueprint $table) {
            $table->dropColumn('row_version');
        });

        Schema::table('aktivitas_belajar', function (Blueprint $table) {
            $table->dropColumn('row_version');
        });

        Schema::table('tugas', function (Blueprint $table) {
            $table->dropColumn('row_version');
        });
    }
};
