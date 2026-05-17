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
        Schema::create('log_penyelia', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_penyelia')->nullable()->index('id_penyelia');
            $table->integer('id_map')->nullable()->index('log_penyelia_ibfk_2');
            $table->integer('status')->nullable();
            $table->string('message')->nullable();
            $table->string('note')->nullable();
            $table->string('document')->nullable();
            $table->integer('flag')->nullable();
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
        Schema::dropIfExists('log_penyelia');
    }
};
