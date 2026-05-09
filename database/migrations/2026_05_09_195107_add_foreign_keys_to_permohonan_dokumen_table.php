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
        Schema::table('permohonan_dokumen', function (Blueprint $table) {
            $table->foreign(['id_permohonan'], 'permohonan_dokumen_ibfk_1')->references(['id_permohonan'])->on('permohonan')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permohonan_dokumen', function (Blueprint $table) {
            $table->dropForeign('permohonan_dokumen_ibfk_1');
        });
    }
};
