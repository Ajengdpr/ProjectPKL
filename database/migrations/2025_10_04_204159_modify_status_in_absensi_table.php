<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // It's safer to use a raw statement for ENUM modification
        // as Doctrine DBAL can have issues.
        DB::statement("ALTER TABLE absensi MODIFY COLUMN status ENUM('Hadir', 'Izin', 'Cuti', 'Sakit', 'Terlambat', 'Tugas Luar', 'alpha') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to the original definition
        DB::statement("ALTER TABLE absensi MODIFY COLUMN status ENUM('Hadir', 'Izin', 'Cuti', 'Sakit', 'Terlambat', 'Tugas Luar') NOT NULL");
    }
};