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
        Schema::create('penyelia_petugas', function (Blueprint $table) {
            $table->integer('id_petugas', true);
            $table->integer('id_user')->nullable();
            $table->integer('id_map')->nullable()->index('id_map');
            $table->integer('id_penyelia')->nullable()->index('id_penyelia');
            $table->integer('status')->nullable();
            $table->integer('created_by')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penyelia_petugas');
    }
};
