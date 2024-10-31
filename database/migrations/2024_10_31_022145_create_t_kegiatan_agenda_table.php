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
        Schema::create('t_kegiatan_agenda', function (Blueprint $table) {
            $table->unsignedBigInteger('agenda_id')->index();
            $table->unsignedBigInteger('kegiatan_id')->index();
            $table->enum('status', ['Belum', 'Berjalan', 'Selesai']);

            $table->foreign('agenda_id')->references('agenda_id')->on('t_agenda');
            $table->foreign('kegiatan_id')->references('kegiatan_id')->on('t_kegiatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_kegiatan_agenda');
    }
};
