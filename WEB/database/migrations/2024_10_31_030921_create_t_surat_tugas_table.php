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
        Schema::create('t_surat_tugas', function (Blueprint $table) {
            $table->unsignedBigInteger('dokumen_id')->index();
            $table->unsignedBigInteger('kegiatan_id')->index();
            $table->timestamps();

            $table->foreign('dokumen_id')->references('dokumen_id')->on('m_dokumen')->onDelete('cascade');
            $table->foreign('kegiatan_id')->references('kegiatan_id')->on('t_kegiatan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_surat_tugas');
    }
};
