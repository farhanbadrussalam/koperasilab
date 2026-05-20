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
            $table->tinyInteger('is_surpeng_signed')->after('is_surat_tugas_signed')->nullable();
            $table->dateTime('verify_surpeng_at')->after('verify_pengajuan_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penyelia', function (Blueprint $table) {
            $table->dropColumn('is_surpeng_signed');
            $table->dropColumn('verify_surpeng_at');
        });
    }
};
