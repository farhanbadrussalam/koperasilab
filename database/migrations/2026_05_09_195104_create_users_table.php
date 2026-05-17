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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->longText('satuankerja_id')->nullable();
            $table->unsignedBigInteger('id_perusahaan')->nullable()->index('users_ibfk_1');
            $table->tinyInteger('verifikasi_perusahaan')->nullable()->comment('1=valid, 2=tidakvalid, null=belum diverifikasi');
            $table->string('name');
            $table->integer('status')->nullable()->default(1);
            $table->string('jabatan')->nullable();
            $table->longText('jobs')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('google_id')->nullable();
            $table->string('password')->nullable();
            $table->text('ttd')->nullable();
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->rememberToken();
            $table->integer('realtime_notifications')->nullable()->default(1);
            $table->timestamps();
            $table->timestamp('selesai_at')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
