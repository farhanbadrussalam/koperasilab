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
        Schema::create('permohonan_dokumen', function (Blueprint $table) {
            $table->integer('id_dokumen', true);
            $table->unsignedBigInteger('id_permohonan')->nullable()->index('id_permohonan');
            $table->bigInteger('id_kontrak')->nullable();
            $table->integer('id_doc_template')->nullable();
            $table->integer('periode')->nullable();
            $table->string('nomer')->nullable();
            $table->string('nama')->nullable();
            $table->integer('status')->nullable();
            $table->string('jenis')->nullable();
            $table->text('ttd')->nullable();
            $table->integer('ttd_by')->nullable();
            $table->text('catatan')->nullable();
            $table->longText('variables')->nullable();
            $table->longText('content_value')->nullable();
            $table->integer('created_by')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permohonan_dokumen');
    }
};
