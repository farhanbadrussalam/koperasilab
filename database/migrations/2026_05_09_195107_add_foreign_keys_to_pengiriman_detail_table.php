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
        Schema::table('pengiriman_detail', function (Blueprint $table) {
            $table->foreign(['id_pengiriman'], 'pengiriman_detail_ibfk_1')->references(['id_pengiriman'])->on('pengiriman')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengiriman_detail', function (Blueprint $table) {
            $table->dropForeign('pengiriman_detail_ibfk_1');
        });
    }
};
