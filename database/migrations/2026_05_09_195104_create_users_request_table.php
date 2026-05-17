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
        Schema::create('users_request', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_user')->nullable();
            $table->integer('id_perusahaan')->nullable();
            $table->tinyInteger('status')->nullable()->default(1)->comment('1=pending 2=approve, 99=tidakvalid');
            $table->enum('jenis', ['lama', 'baru'])->nullable();
            $table->dateTime('verify_at')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_request');
    }
};
