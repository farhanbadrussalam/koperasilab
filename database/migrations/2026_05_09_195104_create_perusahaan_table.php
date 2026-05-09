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
        Schema::create('perusahaan', function (Blueprint $table) {
            $table->bigIncrements('id_perusahaan');
            $table->string('nama_perusahaan');
            $table->string('npwp_perusahaan')->nullable();
            $table->string('kode_perusahaan')->nullable();
            $table->string('email')->nullable();
            $table->integer('surat_kuasa')->nullable();
            $table->integer('status')->nullable();
            $table->timestamp('confirm_at')->nullable();
            $table->integer('confirm_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perusahaan');
    }
};
