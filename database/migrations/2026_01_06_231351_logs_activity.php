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
        Schema::create('logs_activity', function (Blueprint $table) {
            $table->id();

            // 1. KATEGORI & LEVEL
            $table->string('log_name')->default('default');
            // Contoh isi: 'auth', 'transaction', 'system', 'audit'

            $table->string('log_type')->nullable();
            // Contoh isi: 'info', 'warning', 'error', 'critical'

            // 2. PELAKU (Who?)
            // Menggunakan user_id bisa nullable (karena tamu/gagal login belum tentu punya ID)
            $table->unsignedBigInteger('causer_id')->nullable();
            $table->string('causer_type')->nullable(); // Contoh: 'App\Models\User'

            // 3. TARGET (On What?)
            // Objek apa yang diubah? (Polymorphic)
            // Ini akan membuat kolom 'subject_id' dan 'subject_type'
            $table->nullableMorphs('subject');

            // 4. DESKRIPSI (What happened?)
            $table->text('description');
            // Contoh: "User X melakukan login" atau "Data invoice #123 diupdate"

            // 5. DATA FLEKSIBEL (The Magic Field)
            $table->json('properties')->nullable();
            // Disini tempat menyimpan: Data lama vs baru, IP Address, Browser, dll.

            // 6. KONTEKS TAMBAHAN (Opsional tapi penting untuk Security)
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable(); // Info browser/device

            $table->timestamps();

            // Indexing untuk performa pencarian cepat
            $table->index('log_name');
            $table->index(['subject_id', 'subject_type']);
            $table->index('causer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
