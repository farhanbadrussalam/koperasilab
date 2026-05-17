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
        Schema::table('kontrak_tld', function (Blueprint $table) {
            $table->foreign(['id_kontrak'], 'kontrak_tld_ibfk_1')->references(['id_kontrak'])->on('kontrak')->onUpdate('cascade')->onDelete('no action');
            $table->foreign(['id_pengguna'], 'kontrak_tld_ibfk_2')->references(['id_pengguna'])->on('master_pengguna')->onUpdate('cascade')->onDelete('no action');
            $table->foreign(['id_divisi'], 'kontrak_tld_ibfk_3')->references(['id_divisi'])->on('master_divisi')->onUpdate('cascade')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kontrak_tld', function (Blueprint $table) {
            $table->dropForeign('kontrak_tld_ibfk_1');
            $table->dropForeign('kontrak_tld_ibfk_2');
            $table->dropForeign('kontrak_tld_ibfk_3');
        });
    }
};
