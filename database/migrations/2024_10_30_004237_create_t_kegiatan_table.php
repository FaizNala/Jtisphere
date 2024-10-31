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
        Schema::table('t_kegiatan', function (Blueprint $table) {
            $table->id('kegiatan_id');
            $table->unsignedBigInteger('kategori_id')->index();
            $table->string('kegiatan_nama');
            $table->enum('Status', ['Belum', 'Berjalan', 'Selesai']);
            $table->text('Deskripsi');

            $table->foreign('kategori_id')->references('kategori_id')->on('m_kategori');
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
