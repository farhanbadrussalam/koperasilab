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
        Schema::create('master_jenislayanan', function (Blueprint $table) {
            $table->increments('id_jenisLayanan');
            $table->string('name')->nullable();
            $table->integer('parent')->nullable();
            $table->longText('jobs')->nullable();
            $table->longText('jobs_paralel')->nullable();
            $table->integer('jobs_paralel_point')->nullable();
            $table->integer('status')->nullable();
            $table->string('alias', 3)->nullable();
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
        Schema::dropIfExists('master_jenislayanan');
    }
};
