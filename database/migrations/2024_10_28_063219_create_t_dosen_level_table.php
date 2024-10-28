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
        Schema::create('t_dosen_level', function (Blueprint $table) {
            $table->unsignedBigInteger('dosen_id')->index();
            $table->unsignedBigInteger('level_id')->index();
            $table->timestamps();


            $table->foreign('dosen_id')->references('dosen_id')->on('m_dosen')->onDelete('cascade');
            $table->foreign('level_id')->references('level_id')->on('m_level')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_dosen_level');
    }
};
