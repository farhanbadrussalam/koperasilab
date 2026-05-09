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
        Schema::create('kontrak_periode', function (Blueprint $table) {
            $table->integer('id_periode', true);
            $table->unsignedBigInteger('id_kontrak')->nullable()->index('id_kontrak');
            $table->integer('periode')->nullable();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->unsignedBigInteger('id_permohonan')->nullable()->index('id_permohonan')->comment('Untuk permohonan evaluasi');
            $table->string('nomer_surpeng')->nullable();
            $table->integer('status')->nullable();
            $table->integer('selesai')->nullable();
            $table->integer('count_tld')->nullable();
            $table->integer('created_by')->nullable();
            $table->dateTime('created_surpeng_at')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kontrak_periode');
    }
};
