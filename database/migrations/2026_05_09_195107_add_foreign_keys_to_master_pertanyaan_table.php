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
        Schema::table('master_pertanyaan', function (Blueprint $table) {
            $table->foreign(['id_layananjasa'], 'master_pertanyaan_ibfk_1')->references(['id_layanan'])->on('master_layanan_jasa')->onUpdate('cascade')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_pertanyaan', function (Blueprint $table) {
            $table->dropForeign('master_pertanyaan_ibfk_1');
        });
    }
};
