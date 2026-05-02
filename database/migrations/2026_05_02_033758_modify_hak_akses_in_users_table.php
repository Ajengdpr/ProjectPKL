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
        Schema::table('users', function (Blueprint $table) {
            $table->string('hak_akses', 20)->default('pegawai')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Kita tidak bisa dengan mudah kembali ke ENUM yang spesifik tanpa detail lengkap, 
            // tapi biasanya VARCHAR sudah cukup aman.
        });
    }
};
