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
        Schema::create('kontrak', function (Blueprint $table) {
            $table->bigIncrements('id_kontrak');
            $table->unsignedBigInteger('id_layanan')->nullable()->index('kontrak_ibfk_1');
            $table->unsignedInteger('id_keuangan')->nullable()->index('kontrak_ibfk_2');
            $table->unsignedInteger('jenis_layanan_1')->nullable()->index('kontrak_ibfk_3');
            $table->unsignedInteger('jenis_layanan_2')->nullable()->index('kontrak_ibfk_4');
            $table->string('tipe_kontrak', 50)->nullable();
            $table->string('no_kontrak', 100)->nullable();
            $table->integer('jenis_tld')->nullable()->index('kontrak_ibfk_7');
            $table->longText('periode_pemakaian')->nullable();
            $table->longText('periode_next')->nullable();
            $table->integer('jumlah_pengguna')->nullable();
            $table->integer('jumlah_kontrol')->nullable();
            $table->integer('harga_layanan')->nullable();
            $table->text('ttd')->nullable();
            $table->unsignedBigInteger('ttd_by')->nullable()->index('kontrak_ibfk_5');
            $table->integer('total_harga')->nullable();
            $table->integer('status');
            $table->string('note')->nullable();
            $table->integer('file_lhu')->nullable();
            $table->string('id_pelanggan')->nullable();
            $table->integer('is_have_tld')->nullable()->default(1);
            $table->tinyInteger('is_zerocek')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index('kontrak_ibfk_6');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kontrak');
    }
};
