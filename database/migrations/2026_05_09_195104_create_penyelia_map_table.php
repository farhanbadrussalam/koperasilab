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
        Schema::create('penyelia_map', function (Blueprint $table) {
            $table->integer('id_map', true);
            $table->integer('id_penyelia')->nullable()->index('id_penyelia');
            $table->integer('id_jobs')->nullable()->index('penyelia_map_ibfk_2');
            $table->integer('order')->nullable();
            $table->integer('status')->nullable()->default(0)->comment('1 = selesai');
            $table->integer('point_jobs')->nullable();
            $table->integer('done_by')->nullable();
            $table->dateTime('done_at')->nullable();
            $table->text('note')->nullable();
            $table->integer('created_by')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penyelia_map');
    }
};
