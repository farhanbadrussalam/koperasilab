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
        Schema::create('permohonan_detail', function (Blueprint $table) {
            $table->integer('id', true);
            $table->bigInteger('id_permohonan')->nullable();
            $table->bigInteger('id_pengguna_divisi')->nullable();
            $table->integer('id_tld')->nullable();
            $table->enum('jenis', ['pengguna', 'kontrol'])->nullable();
            $table->integer('status')->nullable();
            $table->enum('type', ['baru', 'ganti', 'lama'])->nullable();
            $table->bigInteger('pengguna_lama')->nullable();
            $table->bigInteger('created_by')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permohonan_detail');
    }
};
