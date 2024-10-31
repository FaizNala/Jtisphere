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
        Schema::table('t_dosen_kegiatan', function (Blueprint $table) {
            $table->id('dosen_kegiatan_id');
            $table->unsignedBigInteger('kegiatan_id')->index();
            $table->unsignedBigInteger('dosen_id')->index();
            $table->unsignedBigInteger('peran_id')->index();

            $table->foreign('kegiatan_id')->references('kegiatan_id')->on('t_kegiatan');
            $table->foreign('dosen_id')->references('dosen_id')->on('m_dosen');
            $table->foreign('peran_id')->references('peran_id')->on('m_peran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_dosen_kegiatan');
    }
};
