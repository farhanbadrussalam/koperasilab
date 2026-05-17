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
        Schema::create('master_tld', function (Blueprint $table) {
            $table->integer('id_tld', true);
            $table->string('no_seri_tld')->nullable();
            $table->string('merk')->nullable();
            $table->string('jenis')->nullable();
            $table->timestamp('tanggal_pengadaan')->nullable();
            $table->integer('kepemilikan')->nullable();
            $table->string('digunakan')->nullable();
            $table->integer('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_tld');
    }
};
