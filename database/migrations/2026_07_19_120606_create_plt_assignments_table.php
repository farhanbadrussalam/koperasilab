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
        Schema::create('plt_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_user_id')->nullable()->comment('Manajer Asli (jika ada)');
            $table->unsignedBigInteger('plt_user_id')->comment('Manajer Pengganti/PLT');
            $table->string('role_name', 100)->default('Manager Keuangan')->comment('Peran yang digantikan');
            $table->dateTime('start_date')->comment('Mulai Berlaku');
            $table->dateTime('end_date')->comment('Akhir Berlaku');
            $table->string('surat_tugas_path')->nullable()->comment('Berkas Fisik/PDF Penunjukan');
            $table->tinyInteger('status')->default(1)->comment('1: Aktif, 0: Dicabut');
            $table->timestamps();
            
            // Relasi (Opsional, asalkan nama tabel users)
            $table->foreign('plt_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('original_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plt_assignments');
    }
};
