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
        Schema::create('permohonan_tandaterima', function (Blueprint $table) {
            $table->unsignedBigInteger('id_permohonan')->index('id_permohonan');
            $table->integer('id_pertanyaan')->index('permohonan_tandaterima_ibfk_2');
            $table->text('jawaban')->nullable();
            $table->string('note')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index('permohonan_tandaterima_ibfk_3');
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permohonan_tandaterima');
    }
};
