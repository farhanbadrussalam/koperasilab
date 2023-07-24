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
        Schema::create('jadwal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layananjasa_id');
            $table->string('jenislayanan');
            $table->integer('tarif');
            $table->dateTime('date_mulai');
            $table->dateTime('date_selesai');
            $table->integer('kuota');
            $table->integer('petugas_id');
            $table->string('dokumen');
            $table->integer('status');
            $table->integer('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal');
    }
};
