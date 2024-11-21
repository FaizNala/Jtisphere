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
        Schema::create('t_agenda_dosen', function (Blueprint $table) {
            $table->id('agenda_dosen_id');
            $table->unsignedBigInteger('agenda_id')->index();
            $table->unsignedBigInteger('dosen_id')->index();
            $table->timestamps();

            $table->foreign('agenda_id')->references('agenda_id')->on('t_agenda');
            $table->foreign('dosen_id')->references('dosen_id')->on('m_dosen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_agenda_dosen');
    }
};
