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
        Schema::create('master_media', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('file_hash');
            $table->string('file_ori');
            $table->integer('file_size');
            $table->string('file_type');
            $table->string('file_path', 100)->nullable();
            $table->integer('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_media');
    }
};
