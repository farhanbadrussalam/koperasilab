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
        Schema::create('detail_permohonan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id');
            $table->integer('status');
            $table->integer('flag');
            $table->text('note')->nullable();
            $table->string('surat_terbitan')->nullable();
            $table->string('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_permohonan');
    }
};
