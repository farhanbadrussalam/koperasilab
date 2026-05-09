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
        Schema::table('permohonan_tandaterima', function (Blueprint $table) {
            $table->foreign(['id_permohonan'], 'permohonan_tandaterima_ibfk_1')->references(['id_permohonan'])->on('permohonan')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['id_pertanyaan'], 'permohonan_tandaterima_ibfk_2')->references(['id_pertanyaan'])->on('master_pertanyaan')->onUpdate('cascade')->onDelete('no action');
            $table->foreign(['created_by'], 'permohonan_tandaterima_ibfk_3')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permohonan_tandaterima', function (Blueprint $table) {
            $table->dropForeign('permohonan_tandaterima_ibfk_1');
            $table->dropForeign('permohonan_tandaterima_ibfk_2');
            $table->dropForeign('permohonan_tandaterima_ibfk_3');
        });
    }
};
