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
        Schema::create('master_pengguna', function (Blueprint $table) {
            $table->integer('id_pengguna', true);
            $table->longText('id_radiasi')->nullable();
            $table->unsignedBigInteger('id_perusahaan')->nullable()->index('master_pengguna_ibfk_1');
            $table->string('kode_lencana')->nullable();
            $table->string('nik')->nullable();
            $table->string('name')->nullable();
            $table->integer('id_divisi')->nullable();
            $table->string('jenis_kelamin', 10)->nullable();
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->integer('ktp')->nullable();
            $table->string('keterangan')->nullable();
            $table->integer('status')->nullable();
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
        Schema::dropIfExists('master_pengguna');
    }
};
