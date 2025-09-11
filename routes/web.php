<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AuthController;

Route::get('/', [AuthController::class, 'loginPage'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout']);

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AbsensiController::class, 'index'])->name('dashboard');

    // (opsional) jika masih pakai halaman form per status:
    Route::get('/absen/{status}', [AbsensiController::class, 'create'])
        ->whereIn('status', ['hadir','izin','cuti','sakit','terlambat','tugas-luar'])
        ->name('absen.create');

    Route::post('/absen', [AbsensiController::class, 'store'])->name('absen.store');
});