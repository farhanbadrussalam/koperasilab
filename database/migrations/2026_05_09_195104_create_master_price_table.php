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
        Schema::create('master_price', function (Blueprint $table) {
            $table->integer('id_price', true);
            $table->integer('id_jenisTld')->nullable()->index('master_price_ibfk_1');
            $table->longText('id_jenisLayanan')->nullable();
            $table->string('keterangan')->nullable();
            $table->integer('qty')->nullable();
            $table->integer('price')->nullable();
            $table->integer('created_by')->nullable();
            $table->dateTime('created_date')->nullable();
            $table->dateTime('updated_date')->useCurrentOnUpdate()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_price');
    }
};
