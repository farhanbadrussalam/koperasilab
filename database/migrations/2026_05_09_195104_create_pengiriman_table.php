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
        Schema::create('pengiriman', function (Blueprint $table) {
            $table->string('id_pengiriman')->default('')->primary();
            $table->string('no_resi')->nullable();
            $table->string('jenis_pengiriman')->nullable();
            $table->integer('id_ekspedisi')->nullable()->index('pengiriman_ibfk_2');
            $table->unsignedBigInteger('id_permohonan')->nullable()->index('id_permohonan');
            $table->unsignedBigInteger('id_kontrak')->nullable()->index('pengiriman_ibfk_3');
            $table->integer('tujuan')->nullable();
            $table->integer('alamat')->nullable();
            $table->string('detail_alamat')->nullable();
            $table->integer('status')->nullable();
            $table->integer('periode')->nullable();
            $table->longText('bukti_pengiriman')->nullable();
            $table->longText('bukti_penerima')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index('pengiriman_ibfk_6');
            $table->dateTime('send_at')->nullable();
            $table->dateTime('recived_at')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengiriman');
    }
};
