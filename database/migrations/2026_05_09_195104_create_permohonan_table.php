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
        Schema::create('permohonan', function (Blueprint $table) {
            $table->bigIncrements('id_permohonan');
            $table->unsignedBigInteger('id_layanan')->nullable()->index('permohonan_ibfk_3');
            $table->unsignedBigInteger('id_kontrak')->nullable()->index('permohonan_ibfk_1');
            $table->string('id_pengiriman')->nullable()->index('permohonan_ibfk_4');
            $table->unsignedInteger('id_alamat')->nullable()->index('permohonan_ibfk_5');
            $table->unsignedInteger('jenis_layanan_1')->nullable()->index('permohonan_ibfk_6');
            $table->unsignedInteger('jenis_layanan_2')->nullable()->index('permohonan_ibfk_7');
            $table->string('tipe_kontrak', 50)->nullable();
            $table->integer('jenis_tld')->nullable()->index('permohonan_ibfk_9');
            $table->longText('periode_pemakaian')->nullable();
            $table->longText('periode_next')->nullable();
            $table->integer('periode')->nullable()->comment('Di ambil dari kontrak_periode');
            $table->integer('jumlah_pengguna')->nullable();
            $table->integer('jumlah_kontrol')->nullable();
            $table->integer('harga_layanan')->nullable();
            $table->string('pic')->nullable();
            $table->string('no_hp')->nullable();
            $table->text('ttd')->nullable();
            $table->unsignedBigInteger('ttd_by')->nullable()->index('permohonan_ibfk_8');
            $table->integer('total_harga')->nullable();
            $table->integer('status');
            $table->string('note')->nullable();
            $table->integer('file_lhu')->nullable();
            $table->integer('flag_read')->nullable();
            $table->integer('is_have_tld')->nullable();
            $table->tinyInteger('is_zerocek')->nullable()->default(1);
            $table->unsignedBigInteger('created_by')->nullable()->index('permohonan_ibfk_2');
            $table->timestamp('verify_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permohonan');
    }
};
