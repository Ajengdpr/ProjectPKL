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
        if (!Schema::hasColumn('users', 'hak_akses')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('hak_akses', 20)->default('pegawai')->after('role');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'hak_akses')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('hak_akses');
            });
        }
    }
};
