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
        Schema::table('permohonan_tld', function (Blueprint $table) {
            $table->foreign(['id_permohonan'], 'permohonan_tld_ibfk_1')->references(['id_permohonan'])->on('permohonan')->onUpdate('cascade')->onDelete('no action');
            $table->foreign(['id_pengguna'], 'permohonan_tld_ibfk_2')->references(['id_pengguna'])->on('master_pengguna')->onUpdate('cascade')->onDelete('no action');
            $table->foreign(['id_divisi'], 'permohonan_tld_ibfk_3')->references(['id_divisi'])->on('master_divisi')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['created_by'], 'permohonan_tld_ibfk_4')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permohonan_tld', function (Blueprint $table) {
            $table->dropForeign('permohonan_tld_ibfk_1');
            $table->dropForeign('permohonan_tld_ibfk_2');
            $table->dropForeign('permohonan_tld_ibfk_3');
            $table->dropForeign('permohonan_tld_ibfk_4');
        });
    }
};
