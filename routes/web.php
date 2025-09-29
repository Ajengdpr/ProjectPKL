<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\UserController;

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminAbsensiController;
use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Middleware\IsAdmin;

/* -------------------- PUBLIC (GUEST) -------------------- */
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'loginPage'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

/* -------------------- AUTH (USER BIASA & ADMIN) -------------------- */
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard user (non-admin)
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

    // Statistik
    Route::get('/statistik', [AbsensiController::class, 'statistik'])->name('statistik');
    Route::get('/rekap-absensi', [AbsensiController::class, 'rekap'])->name('rekap.absensi');
    Route::get('/statistik/rekap', [AbsensiController::class, 'rekapHarian'])->name('statistik.rekapHarian');

    // Notifications (contoh)
    Route::get('/notifications', function () {
        $items = [
            ['title' => 'Absensi kamu terekam: Hadir', 'time' => 'Baru saja'],
            ['title' => 'Pengajuan Cuti disetujui',     'time' => '2 jam lalu'],
            ['title' => 'Reminder: Rapat apel pagi',     'time' => 'Kemarin'],
        ];
        return view('notifications', compact('items'));
    })->name('notifications');
});

/* -------------------- ADMIN AREA -------------------- */
Route::middleware(['auth', IsAdmin::class])
    ->prefix('admin')->name('admin.')
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Users (CRUD)
        Route::resource('users', AdminUserController::class)->except(['show']);
        Route::post('users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.reset');

        // Absensi (list/filter/input/edit/hapus/export)
        Route::get('absensi', [AdminAbsensiController::class, 'index'])->name('absensi.index');
        Route::post('absensi', [AdminAbsensiController::class, 'store'])->name('absensi.store');
        Route::get('absensi/{absensi}/edit', [AdminAbsensiController::class, 'edit'])->name('absensi.edit');
        Route::put('absensi/{absensi}', [AdminAbsensiController::class, 'update'])->name('absensi.update');
        Route::delete('absensi/{absensi}', [AdminAbsensiController::class, 'destroy'])->name('absensi.destroy');
        Route::get('absensi/export/csv', [AdminAbsensiController::class, 'exportCsv'])->name('absensi.export.csv');

        // Settings
        Route::get('settings', [AdminSettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [AdminSettingController::class, 'save'])->name('settings.save');

        // Account (profil)
        Route::get('account', [UserController::class, 'account'])->name('account');
    });

/* -------------------- ROOT REDIRECT -------------------- */
Route::get('/', function () {
    if (auth()->check()) {
        return (auth()->user()->role ?? 'user') === 'admin'
            ? redirect()->route('admin.dashboard')
            : redirect()->route('dashboard');
    }
    return redirect()->route('login');
});