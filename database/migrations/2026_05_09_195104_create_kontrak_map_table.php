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
        Schema::create('kontrak_map', function (Blueprint $table) {
            $table->integer('id_map', true);
            $table->integer('id_kontrak');
            $table->integer('id_kontrak_detail')->nullable();
            $table->bigInteger('id_pengguna_divisi')->nullable();
            $table->integer('id_tld')->nullable();
            $table->enum('jenis', ['pengguna', 'kontrol'])->nullable();
            $table->integer('periode');
            $table->integer('created_by')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kontrak_map');
    }
};
