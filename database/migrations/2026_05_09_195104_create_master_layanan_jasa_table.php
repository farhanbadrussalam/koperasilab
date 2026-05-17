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
        Schema::create('master_layanan_jasa', function (Blueprint $table) {
            $table->bigIncrements('id_layanan');
            $table->string('nama_layanan')->nullable();
            $table->integer('status');
            $table->longText('jobs')->nullable();
            $table->integer('satuankerja_id')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_layanan_jasa');
    }
};
