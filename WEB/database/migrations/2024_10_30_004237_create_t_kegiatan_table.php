<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_kegiatan', function (Blueprint $table) {
            $table->id('kegiatan_id');
            $table->unsignedBigInteger('kategori_id')->index();
            $table->unsignedBigInteger('periode_id')->index();
            $table->string('kegiatan_nama');
            $table->text('deskripsi');
            $table->enum('skala', ['Internal','Nasional','Internasional','Lain-Lain']);
            $table->bigInteger('anggaran');
            $table->enum('status', ['Belum', 'Berjalan', 'Selesai']);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->timestamps();

            $table->foreign('kategori_id')->references('kategori_id')->on('m_kategori');
            $table->foreign('periode_id')->references('periode_id')->on('m_periode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_kegiatan');
    }
};
