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
        Schema::create('t_agenda_detail', function (Blueprint $table) {
            $table->unsignedBigInteger('agenda_id')->index();
            $table->unsignedBigInteger('kegiatan_detail_id')->index();
            $table->timestamps();

            $table->foreign('agenda_id')->references('agenda_id')->on('t_agenda');
            $table->foreign('kegiatan_detail_id')->references('kegiatan_detail_id')->on('t_kegiatan_detail');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_agenda_detail');
    }
};
