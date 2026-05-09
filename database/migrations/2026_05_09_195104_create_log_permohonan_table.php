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
        Schema::create('log_permohonan', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_permohonan')->index('log_permohonan_ibfk_1');
            $table->integer('status');
            $table->integer('flag')->nullable();
            $table->text('note')->nullable();
            $table->integer('file')->nullable();
            $table->integer('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_permohonan');
    }
};
