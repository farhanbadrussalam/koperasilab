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
        Schema::create('pengiriman_detail', function (Blueprint $table) {
            $table->integer('id_pengiriman_detail', true);
            $table->string('id_pengiriman')->nullable()->index('id_pengiriman');
            $table->string('jenis')->nullable();
            $table->integer('periode')->nullable();
            $table->longText('list_tld')->nullable();
            $table->string('nomer_surpeng')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengiriman_detail');
    }
};
