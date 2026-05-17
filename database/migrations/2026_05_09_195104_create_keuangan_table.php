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
        Schema::create('keuangan', function (Blueprint $table) {
            $table->increments('id_keuangan');
            $table->unsignedBigInteger('id_permohonan')->nullable()->index('id_permohonan');
            $table->string('id_pengiriman')->nullable()->index('id_pengiriman');
            $table->integer('id_jenis_pembayaran')->nullable()->index('id_jenis_pembayaran');
            $table->longText('variabel_jenis_pembayaran')->nullable();
            $table->string('no_invoice')->nullable();
            $table->integer('status')->nullable();
            $table->integer('ppn')->nullable();
            $table->integer('pph')->nullable();
            $table->longText('document_faktur')->nullable();
            $table->longText('bukti_bayar')->nullable();
            $table->longText('bukti_bayar_pph')->nullable();
            $table->text('ttd')->nullable();
            $table->unsignedBigInteger('ttd_by')->nullable()->index('keuangan_ibfk_7');
            $table->integer('plt')->nullable();
            $table->integer('total_harga')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('verif_at')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index('keuangan_ibfk_6');
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keuangan');
    }
};
