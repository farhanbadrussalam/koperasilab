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
        Schema::create('permohonan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layananjasa_id');
            $table->foreignId('jadwal_id');
            $table->string('no_kontrak');
            $table->string('jenis_layanan');
            $table->integer('tarif');
            $table->string('no_bapeten');
            $table->string('jenis_limbah');
            $table->string('sumber_radioaktif');
            $table->integer('jumlah');
            $table->json('dokumen');
            $table->string('status');
            $table->string('nomor_antrian');
            $table->string('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permohonan');
    }
};
