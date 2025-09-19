<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

Route::get('/', [AuthController::class, 'loginPage'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [AbsensiController::class, 'index'])->name('dashboard');

    // Account (profil)
    Route::get('/account', [UserController::class, 'account'])->name('account');
    Route::post('/account/photo', [UserController::class, 'updatePhoto'])->name('account.photo');
    Route::delete('/account/photo', [UserController::class, 'deletePhoto'])->name('account.photo.delete');

    // Absen
    Route::get('/absen/{status}', [AbsensiController::class, 'create'])
        ->whereIn('status', ['hadir','izin','cuti','sakit','terlambat','tugas-luar'])
        ->name('absen.create');

    Route::post('/absen', [AbsensiController::class, 'store'])->name('absen.store');

    // Statistik absensi
    Route::get('/statistik', [AbsensiController::class, 'statistik'])->name('statistik');
    Route::get('/rekap-absensi', [AbsensiController::class, 'rekap'])->name('rekap.absensi');
    Route::get('/statistik/rekap', [AbsensiController::class, 'rekapHarian'])->name('statistik.rekapHarian');
    // Notifications (sementara dummy data)
    Route::get('/notifications', function () {
        $items = [
            ['title' => 'Absensi kamu terekam: Hadir', 'time' => 'Baru saja'],
            ['title' => 'Pengajuan Cuti disetujui', 'time' => '2 jam lalu'],
            ['title' => 'Reminder: Rapat apel pagi', 'time' => 'Kemarin'],
        ];
        return view('notifications', compact('items'));
    })->name('notifications');
});
