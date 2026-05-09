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
        Schema::create('kontrak_tld', function (Blueprint $table) {
            $table->integer('id_kontrak_tld', true);
            $table->unsignedBigInteger('id_kontrak')->nullable()->index('kontrak_tld_ibfk_1');
            $table->longText('id_tld')->nullable();
            $table->integer('count')->nullable();
            $table->integer('id_pengguna')->nullable()->index('kontrak_tld_ibfk_2');
            $table->integer('id_divisi')->nullable()->index('kontrak_tld_ibfk_3');
            $table->integer('count_tld')->nullable();
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
        Schema::dropIfExists('kontrak_tld');
    }
};
