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
        Schema::table('kontrak_periode', function (Blueprint $table) {
            $table->foreign(['id_kontrak'], 'kontrak_periode_ibfk_1')->references(['id_kontrak'])->on('kontrak')->onUpdate('cascade')->onDelete('no action');
            $table->foreign(['id_permohonan'], 'kontrak_periode_ibfk_2')->references(['id_permohonan'])->on('permohonan')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kontrak_periode', function (Blueprint $table) {
            $table->dropForeign('kontrak_periode_ibfk_1');
            $table->dropForeign('kontrak_periode_ibfk_2');
        });
    }
};
