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
        Schema::table('penyelia', function (Blueprint $table) {
            $table->foreign(['id_permohonan'], 'penyelia_ibfk_1')->references(['id_permohonan'])->on('permohonan')->onUpdate('cascade')->onDelete('no action');
            $table->foreign(['id_pengiriman'], 'penyelia_ibfk_2')->references(['id_pengiriman'])->on('pengiriman')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penyelia', function (Blueprint $table) {
            $table->dropForeign('penyelia_ibfk_1');
            $table->dropForeign('penyelia_ibfk_2');
        });
    }
};
