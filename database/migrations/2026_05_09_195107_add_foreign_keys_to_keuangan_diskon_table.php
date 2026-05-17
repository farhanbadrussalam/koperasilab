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
        Schema::table('keuangan_diskon', function (Blueprint $table) {
            $table->foreign(['id_keuangan'], 'keuangan_diskon_ibfk_1')->references(['id_keuangan'])->on('keuangan')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('keuangan_diskon', function (Blueprint $table) {
            $table->dropForeign('keuangan_diskon_ibfk_1');
        });
    }
};
