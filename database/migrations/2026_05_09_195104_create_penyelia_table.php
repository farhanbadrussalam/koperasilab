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
        Schema::create('penyelia', function (Blueprint $table) {
            $table->integer('id_penyelia', true);
            $table->unsignedBigInteger('id_permohonan')->nullable()->index('penyelia_ibfk_1');
            $table->string('id_pengiriman')->nullable()->index('id_pengiriman');
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->integer('periode')->nullable();
            $table->integer('status')->nullable();
            $table->tinyInteger('is_pengajuan_signed')->nullable();
            $table->tinyInteger('is_surat_tugas_signed')->nullable();
            $table->text('ttd')->nullable();
            $table->integer('ttd_by')->nullable();
            $table->longText('document')->nullable();
            $table->longText('list_tld')->nullable();
            $table->dateTime('verify_surat_tugas_at')->nullable();
            $table->dateTime('verify_pengajuan_at')->nullable();
            $table->integer('created_by')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penyelia');
    }
};
