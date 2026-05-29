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
        Schema::table('penyelia', function (Blueprint $table) {
            $table->tinyInteger('periode_used')->after('periode')->nullable();
            $table->integer('id_kontrak')->after('id_permohonan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penyelia', function (Blueprint $table) {
            $table->dropColumn('periode_used');
            $table->dropColumn('id_kontrak');
        });
    }
};
