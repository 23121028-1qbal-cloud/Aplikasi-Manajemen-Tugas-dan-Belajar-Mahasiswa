<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->bigIncrements('id_notifikasi');
            $table->unsignedBigInteger('id_user');
            $table->string('tipe', 50);
            $table->string('judul', 150);
            $table->text('pesan');
            $table->string('resource_type', 50)->nullable();
            $table->unsignedBigInteger('resource_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->string('dedup_key', 191);
            $table->dateTime('tanggal_dibuat');
            $table->timestamps();

            $table->foreign('id_user')
                ->references('id_user')
                ->on('users')
                ->restrictOnDelete();

            $table->unique(['id_user', 'dedup_key'], 'notifikasi_dedup_unique');
            $table->index(['id_user', 'is_read', 'tanggal_dibuat'], 'notifikasi_inbox_index');
            $table->index('tanggal_dibuat', 'notifikasi_retention_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
    }
};
