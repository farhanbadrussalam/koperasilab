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
        Schema::table('log_permohonan', function (Blueprint $table) {
            $table->foreign(['id_permohonan'], 'log_permohonan_ibfk_1')->references(['id_permohonan'])->on('permohonan')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_permohonan', function (Blueprint $table) {
            $table->dropForeign('log_permohonan_ibfk_1');
        });
    }
};
