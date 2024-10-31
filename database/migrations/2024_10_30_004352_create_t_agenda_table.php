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
        Schema::table('t_agenda', function (Blueprint $table) {
            $table->id('agenda_id');
            $table->String('nama');
            $table->dateTime('Tanggal_mulai');
            $table->dateTime('Tanggal_selesai');
            $table->decimal('progress_persen',[3,2]);
            $table->text('progress_deskripsi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_agenda');
    }
};
