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
        Schema::create('setting_layanan', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name')->nullable();
            $table->longText('jobs')->nullable();
            $table->longText('jobs_paralel')->nullable();
            $table->tinyInteger('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setting_layanan');
    }
};
