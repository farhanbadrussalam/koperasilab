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
        if(Schema::hasColumn('perusahaan', 'surat_kuasa')){
            Schema::table('perusahaan', function (Blueprint $table) {
                $table->dropColumn('surat_kuasa');
            });
        }

        if(Schema::hasColumn('perusahaan', 'confirm_at')){
            Schema::table('perusahaan', function (Blueprint $table) {
                $table->dropColumn('confirm_at');
            });
        }

        if(Schema::hasColumn('perusahaan', 'confirm_by')){
            Schema::table('perusahaan', function (Blueprint $table) {
                $table->dropColumn('confirm_by');
            });
        }

        if(!Schema::hasColumn('perusahaan', 'stempel')){
            Schema::table('perusahaan', function (Blueprint $table) {
                $table->integer('stempel')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perusahaan', function (Blueprint $table) {
            $table->dropColumn('stempel');
        });
    }
};
