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
        Schema::table('penyelia_map', function (Blueprint $table) {
            $table->foreign(['id_penyelia'], 'penyelia_map_ibfk_1')->references(['id_penyelia'])->on('penyelia')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['id_jobs'], 'penyelia_map_ibfk_2')->references(['id_jobs'])->on('master_jobs')->onUpdate('cascade')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penyelia_map', function (Blueprint $table) {
            $table->dropForeign('penyelia_map_ibfk_1');
            $table->dropForeign('penyelia_map_ibfk_2');
        });
    }
};
