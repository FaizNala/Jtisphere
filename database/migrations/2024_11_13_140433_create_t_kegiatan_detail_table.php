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
        Schema::create('t_kegiatan_detail', function (Blueprint $table) {
            $table->id('kegiatan_detail_id');
            $table->unsignedBigInteger('periode_id')->index();
            $table->unsignedBigInteger('kegiatan_id')->index();
            $table->enum('status', ['Belum','Berjalan','Selesai']);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->timestamps();

            $table->foreign('periode_id')->references('periode_id')->on('m_periode');
            $table->foreign('kegiatan_id')->references('kegiatan_id')->on('m_kegiatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_kegiatan_detail');
    }
};
