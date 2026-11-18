<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guru_dosen', function (Blueprint $table) {
            $table->bigIncrements('id_guru_dosen');
            $table->unsignedBigInteger('id_user')->unique();
            $table->string('nip_guru', 30)->unique();
            $table->string('nama_lengkap', 100);
            $table->boolean('is_archived')->default(false);
            $table->timestamp('tanggal_arsip')->nullable();
            $table->timestamps();

            $table->foreign('id_user')
                ->references('id_user')
                ->on('users')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru_dosen');
    }
};
