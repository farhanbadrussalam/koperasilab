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
        Schema::create('documents', function (Blueprint $table) {
            $table->integer('id_doc', true);
            $table->integer('id_perusahaan')->nullable();
            $table->string('name')->nullable();
            $table->string('jenis')->nullable();
            $table->longText('pertanyaan')->nullable();
            $table->integer('status')->nullable()->comment('1 = active, 99 = remove');
            $table->integer('version')->nullable();
            $table->integer('id_doc_version')->nullable();
            $table->longText('content');
            $table->integer('id_header')->nullable();
            $table->integer('id_footer')->nullable();
            $table->enum('orientation', ['portrait', 'landscape'])->nullable();
            $table->string('alias', 10)->nullable();
            $table->longText('variables')->nullable();
            $table->string('no_formulir')->nullable();
            $table->string('view')->nullable();
            $table->string('created_by')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
