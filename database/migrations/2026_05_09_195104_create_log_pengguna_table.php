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
        Schema::create('log_pengguna', function (Blueprint $table) {
            $table->integer('id_log_pengguna')->primary();
            $table->integer('id_pengguna')->nullable();
            $table->integer('id_pengganti')->nullable();
            $table->integer('status')->nullable();
            $table->integer('periode')->nullable();
            $table->string('message')->nullable();
            $table->string('note')->nullable();
            $table->string('created_by')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_pengguna');
    }
};
