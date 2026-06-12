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
        Schema::table('penyelia_map', function (Blueprint $table) {
            $table->boolean('is_stopped')->default(false)->comment('Menandai bahwa job paralel ini dihentikan karena tidak ada periode berikutnya (N+2)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penyelia_map', function (Blueprint $table) {
            $table->dropColumn('is_stopped');
        });
    }
};
