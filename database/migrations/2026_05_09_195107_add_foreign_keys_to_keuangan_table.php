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
        Schema::table('keuangan', function (Blueprint $table) {
            $table->foreign(['id_permohonan'], 'keuangan_ibfk_1')->references(['id_permohonan'])->on('permohonan')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['id_pengiriman'], 'keuangan_ibfk_2')->references(['id_pengiriman'])->on('pengiriman')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['created_by'], 'keuangan_ibfk_6')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('no action');
            $table->foreign(['ttd_by'], 'keuangan_ibfk_7')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('no action');
            $table->foreign(['id_jenis_pembayaran'], 'keuangan_ibfk_8')->references(['id_jenis_pembayaran'])->on('jenis_pembayaran')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('keuangan', function (Blueprint $table) {
            $table->dropForeign('keuangan_ibfk_1');
            $table->dropForeign('keuangan_ibfk_2');
            $table->dropForeign('keuangan_ibfk_6');
            $table->dropForeign('keuangan_ibfk_7');
            $table->dropForeign('keuangan_ibfk_8');
        });
    }
};
