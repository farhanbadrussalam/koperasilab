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
        Schema::table('kontrak', function (Blueprint $table) {
            $table->foreign(['id_layanan'], 'kontrak_ibfk_1')->references(['id_layanan'])->on('master_layanan_jasa')->onUpdate('cascade')->onDelete('no action');
            $table->foreign(['id_keuangan'], 'kontrak_ibfk_2')->references(['id_keuangan'])->on('keuangan')->onUpdate('cascade')->onDelete('no action');
            $table->foreign(['jenis_layanan_1'], 'kontrak_ibfk_3')->references(['id_jenisLayanan'])->on('master_jenislayanan')->onUpdate('cascade')->onDelete('no action');
            $table->foreign(['jenis_layanan_2'], 'kontrak_ibfk_4')->references(['id_jenisLayanan'])->on('master_jenislayanan')->onUpdate('cascade')->onDelete('no action');
            $table->foreign(['ttd_by'], 'kontrak_ibfk_5')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('no action');
            $table->foreign(['created_by'], 'kontrak_ibfk_6')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('no action');
            $table->foreign(['jenis_tld'], 'kontrak_ibfk_7')->references(['id_jenisTld'])->on('master_jenistld')->onUpdate('cascade')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kontrak', function (Blueprint $table) {
            $table->dropForeign('kontrak_ibfk_1');
            $table->dropForeign('kontrak_ibfk_2');
            $table->dropForeign('kontrak_ibfk_3');
            $table->dropForeign('kontrak_ibfk_4');
            $table->dropForeign('kontrak_ibfk_5');
            $table->dropForeign('kontrak_ibfk_6');
            $table->dropForeign('kontrak_ibfk_7');
        });
    }
};
