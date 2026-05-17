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
        Schema::table('penyelia_petugas', function (Blueprint $table) {
            $table->foreign(['id_penyelia'], 'penyelia_petugas_ibfk_1')->references(['id_penyelia'])->on('penyelia')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['id_map'], 'penyelia_petugas_ibfk_2')->references(['id_map'])->on('penyelia_map')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penyelia_petugas', function (Blueprint $table) {
            $table->dropForeign('penyelia_petugas_ibfk_1');
            $table->dropForeign('penyelia_petugas_ibfk_2');
        });
    }
};
