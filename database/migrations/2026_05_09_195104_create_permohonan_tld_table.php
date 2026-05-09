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
        Schema::create('permohonan_tld', function (Blueprint $table) {
            $table->integer('id_permohonan_tld', true);
            $table->unsignedBigInteger('id_permohonan')->nullable()->index('permohonan_tld_ibfk_1');
            $table->longText('id_tld')->nullable();
            $table->integer('id_kontrak_tld')->nullable();
            $table->string('tld_tmp')->nullable();
            $table->integer('count')->nullable();
            $table->integer('id_pengguna')->nullable()->index('permohonan_tld_ibfk_2');
            $table->integer('id_divisi')->nullable()->index('id_divisi');
            $table->unsignedBigInteger('created_by')->nullable()->index('permohonan_tld_ibfk_4');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permohonan_tld');
    }
};
