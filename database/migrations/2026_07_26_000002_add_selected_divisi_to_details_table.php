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
        Schema::table('permohonan_detail', function (Blueprint $table) {
            $table->integer('id_divisi_selected')->nullable()->after('id_pengguna_divisi');
            $table->string('kode_lencana_selected')->nullable()->after('id_divisi_selected');
        });

        Schema::table('kontrak_detail', function (Blueprint $table) {
            $table->integer('id_divisi_selected')->nullable()->after('id_pengguna_divisi');
            $table->string('kode_lencana_selected')->nullable()->after('id_divisi_selected');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permohonan_detail', function (Blueprint $table) {
            $table->dropColumn(['id_divisi_selected', 'kode_lencana_selected']);
        });

        Schema::table('kontrak_detail', function (Blueprint $table) {
            $table->dropColumn(['id_divisi_selected', 'kode_lencana_selected']);
        });
    }
};
