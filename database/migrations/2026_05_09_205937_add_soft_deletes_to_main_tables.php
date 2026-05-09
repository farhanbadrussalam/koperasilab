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
            $table->softDeletes();
        });

        Schema::table('master_radiasi', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('master_tld', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('master_ttd', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('master_divisi', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('perusahaan', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('jenis_pembayaran', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('master_ekspedisi', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_pengguna', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('master_radiasi', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('master_tld', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('master_ttd', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('master_divisi', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('perusahaan', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('jenis_pembayaran', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('master_ekspedisi', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
