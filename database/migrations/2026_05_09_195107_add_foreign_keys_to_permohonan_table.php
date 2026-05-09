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
        Schema::table('permohonan', function (Blueprint $table) {
            $table->foreign(['id_kontrak'], 'permohonan_ibfk_1')->references(['id_kontrak'])->on('kontrak')->onUpdate('cascade')->onDelete('no action');
            $table->foreign(['created_by'], 'permohonan_ibfk_2')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('no action');
            $table->foreign(['id_layanan'], 'permohonan_ibfk_3')->references(['id_layanan'])->on('master_layanan_jasa')->onUpdate('cascade')->onDelete('no action');
            $table->foreign(['id_pengiriman'], 'permohonan_ibfk_4')->references(['id_pengiriman'])->on('pengiriman')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['id_alamat'], 'permohonan_ibfk_5')->references(['id_alamat'])->on('master_alamat')->onUpdate('cascade')->onDelete('no action');
            $table->foreign(['jenis_layanan_1'], 'permohonan_ibfk_6')->references(['id_jenisLayanan'])->on('master_jenislayanan')->onUpdate('cascade')->onDelete('no action');
            $table->foreign(['jenis_layanan_2'], 'permohonan_ibfk_7')->references(['id_jenisLayanan'])->on('master_jenislayanan')->onUpdate('cascade')->onDelete('no action');
            $table->foreign(['ttd_by'], 'permohonan_ibfk_8')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('no action');
            $table->foreign(['jenis_tld'], 'permohonan_ibfk_9')->references(['id_jenisTld'])->on('master_jenistld')->onUpdate('cascade')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permohonan', function (Blueprint $table) {
            $table->dropForeign('permohonan_ibfk_1');
            $table->dropForeign('permohonan_ibfk_2');
            $table->dropForeign('permohonan_ibfk_3');
            $table->dropForeign('permohonan_ibfk_4');
            $table->dropForeign('permohonan_ibfk_5');
            $table->dropForeign('permohonan_ibfk_6');
            $table->dropForeign('permohonan_ibfk_7');
            $table->dropForeign('permohonan_ibfk_8');
            $table->dropForeign('permohonan_ibfk_9');
        });
    }
};
