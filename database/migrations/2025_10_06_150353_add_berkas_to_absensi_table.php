<?php

// File: database/migrations/xxxx_xx_xx_xxxxxx_add_berkas_to_absensi_table.php

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
        Schema::table('absensi', function (Blueprint $table) {
            // Tambahkan baris ini
            $table->string('berkas')->nullable()->after('alasan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            // Tambahkan baris ini untuk bisa rollback
            $table->dropColumn('berkas');
        });
    }
};