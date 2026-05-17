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
        Schema::create('keuangan_diskon', function (Blueprint $table) {
            $table->integer('id_diskon', true);
            $table->unsignedInteger('id_keuangan')->nullable()->index('id_keuangan');
            $table->string('name')->nullable();
            $table->integer('diskon')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keuangan_diskon');
    }
};
