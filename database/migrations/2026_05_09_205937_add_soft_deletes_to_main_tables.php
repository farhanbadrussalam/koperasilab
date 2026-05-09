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

        Schema::table('profiles', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('master_pengguna', 'deleted_at')) {
            Schema::table('master_pengguna', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasColumn('master_radiasi', 'deleted_at')) {
            Schema::table('master_radiasi', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasColumn('master_tld', 'deleted_at')) {
            Schema::table('master_tld', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasColumn('master_ttd', 'deleted_at')) {
            Schema::table('master_ttd', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasColumn('master_divisi', 'deleted_at')) {
            Schema::table('master_divisi', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasColumn('perusahaan', 'deleted_at')) {
            Schema::table('perusahaan', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasColumn('documents', 'deleted_at')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasColumn('jenis_pembayaran', 'deleted_at')) {
            Schema::table('jenis_pembayaran', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasColumn('master_ekspedisi', 'deleted_at')) {
            Schema::table('master_ekspedisi', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasColumn('profiles', 'deleted_at')) {
            Schema::table('profiles', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
