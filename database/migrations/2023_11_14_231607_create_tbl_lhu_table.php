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
        Schema::create('tbl_lhu', function (Blueprint $table) {
            $table->id();
            $table->string('no_kontrak');
            $table->integer('level');
            $table->integer('active');
            $table->string('surat_tugas');
            $table->string('document')->nullabel();
            $table->string('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_lhu');
    }
};
