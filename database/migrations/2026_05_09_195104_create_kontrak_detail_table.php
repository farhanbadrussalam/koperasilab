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
        Schema::create('kontrak_detail', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('id_pengguna_divisi')->nullable();
            $table->integer('id_kontrak')->nullable();
            $table->integer('tld_1')->nullable();
            $table->integer('status_tld_1')->nullable();
            $table->tinyInteger('periode_tld_1')->nullable();
            $table->integer('tld_2')->nullable();
            $table->integer('status_tld_2')->nullable();
            $table->tinyInteger('periode_tld_2')->nullable();
            $table->enum('jenis', ['pengguna', 'kontrol'])->nullable();
            $table->integer('periode')->nullable();
            $table->integer('status')->nullable()->comment('1=active, 2=standby, 99=diganti');
            $table->enum('type', ['baru', 'ganti', 'lama'])->nullable();
            $table->integer('pengguna_lama')->nullable();
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
        Schema::dropIfExists('kontrak_detail');
    }
};
