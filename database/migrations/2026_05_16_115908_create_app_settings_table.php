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
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id('id_setting'); // Primary key
            $table->string('key', 100)->unique(); // Kunci identifikasi setting
            $table->longText('value')->nullable(); // Nilai dari setting
            $table->string('group', 50)->default('general'); // Pengelompokan (identity, finance, dll)
            $table->text('description')->nullable(); // Penjelasan singkat fungsi setting
            $table->timestamps(); // Otomatis membuat created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
