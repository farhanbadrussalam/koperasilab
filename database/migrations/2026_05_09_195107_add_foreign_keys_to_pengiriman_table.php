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
        Schema::table('pengiriman', function (Blueprint $table) {
            $table->foreign(['id_ekspedisi'], 'pengiriman_ibfk_2')->references(['id_ekspedisi'])->on('master_ekspedisi')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['id_kontrak'], 'pengiriman_ibfk_3')->references(['id_kontrak'])->on('kontrak')->onUpdate('cascade')->onDelete('no action');
            $table->foreign(['created_by'], 'pengiriman_ibfk_6')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengiriman', function (Blueprint $table) {
            $table->dropForeign('pengiriman_ibfk_2');
            $table->dropForeign('pengiriman_ibfk_3');
            $table->dropForeign('pengiriman_ibfk_6');
        });
    }
};
