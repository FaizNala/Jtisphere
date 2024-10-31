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
        Schema::create('m_dokumen', function (Blueprint $table) {
            $table->id('dokumen_id');
            $table->string('dokumen_nama', 255);
            $table->enum('dokumen_kategori', ['Surat Tugas', 'Bukti Tugas']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_dokumen');
    }
};
