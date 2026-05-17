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
        Schema::create('master_pertanyaan', function (Blueprint $table) {
            $table->integer('id_pertanyaan', true);
            $table->unsignedBigInteger('id_layananjasa')->nullable()->index('master_pertanyaan_ibfk_1');
            $table->string('pertanyaan')->nullable();
            $table->integer('type')->nullable();
            $table->integer('mandatory')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_pertanyaan');
    }
};
