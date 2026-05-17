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
        Schema::table('master_price', function (Blueprint $table) {
            $table->foreign(['id_jenisTld'], 'master_price_ibfk_1')->references(['id_jenisTld'])->on('master_jenistld')->onUpdate('cascade')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_price', function (Blueprint $table) {
            $table->dropForeign('master_price_ibfk_1');
        });
    }
};
