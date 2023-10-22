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
        Schema::create('petugas_layanan', function (Blueprint $table) {
            $table->id();
            $table->integer('lab_id');
            $table->integer('satuankerja_id');
            $table->integer('user_id');
            $table->integer('status_verif');
            $table->integer('status');
            $table->integer('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petugas_layanan');
    }
};
