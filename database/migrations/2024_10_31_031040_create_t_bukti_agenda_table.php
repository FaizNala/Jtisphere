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
        Schema::create('t_bukti_agenda', function (Blueprint $table) {
            $table->unsignedBigInteger('dokumen_id')->index();
            $table->unsignedBigInteger('agenda_dosen_id')->index();
            $table->timestamps();

            $table->foreign('dokumen_id')->references('dokumen_id')->on('m_dokumen')->onDelete('cascade');
            $table->foreign('agenda_dosen_id')->references('agenda_dosen_id')->on('t_agenda_dosen')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_bukti_agenda');
    }
};
