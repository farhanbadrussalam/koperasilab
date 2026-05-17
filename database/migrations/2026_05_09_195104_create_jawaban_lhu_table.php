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
        Schema::create('jawaban_lhu', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('lhu_id')->nullable();
            $table->integer('pertanyaan_id')->nullable()->index('pertanyaan_id');
            $table->string('jawaban')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jawaban_lhu');
    }
};
