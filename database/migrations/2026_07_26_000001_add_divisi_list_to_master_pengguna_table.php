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
        Schema::table('master_pengguna', function (Blueprint $table) {
            $table->json('divisi_list')->nullable()->after('id_divisi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_pengguna', function (Blueprint $table) {
            $table->dropColumn('divisi_list');
        });
    }
};
