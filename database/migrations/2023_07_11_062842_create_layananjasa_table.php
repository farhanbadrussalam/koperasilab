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
        Schema::create('layananjasa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('satuankerja_id');
            $table->foreignId('user_id');
            $table->string('nama_layanan')->nullable();
            $table->json('jenis_layanan');
            $table->integer('status');
            $table->string('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('layananjasa');
    }
};
