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
        Schema::table('log_penyelia', function (Blueprint $table) {
            $table->foreign(['id_penyelia'], 'log_penyelia_ibfk_1')->references(['id_penyelia'])->on('penyelia')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['id_map'], 'log_penyelia_ibfk_2')->references(['id_map'])->on('penyelia_map')->onUpdate('cascade')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_penyelia', function (Blueprint $table) {
            $table->dropForeign('log_penyelia_ibfk_1');
            $table->dropForeign('log_penyelia_ibfk_2');
        });
    }
};
