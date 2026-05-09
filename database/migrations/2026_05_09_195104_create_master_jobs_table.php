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
        Schema::create('master_jobs', function (Blueprint $table) {
            $table->integer('id_jobs', true);
            $table->integer('id_layanan')->nullable();
            $table->string('name')->nullable();
            $table->tinyInteger('order')->nullable();
            $table->integer('status')->nullable();
            $table->integer('upload_doc')->nullable();
            $table->string('color', 7)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_jobs');
    }
};
