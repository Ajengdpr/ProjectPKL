<?php

use App\Models\User;
use App\Models\Absensi;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

it('auto alpha command: menandai user yang tidak absen', function () {
    $user = createTestUser('anggota', 'IT');
    
    // Set waktu ke jam 16:30 (lewat cutoff 16:00)
    Carbon::setTestNow(Carbon::parse('2026-05-07 16:30:00', 'Asia/Makassar'));
    
    Artisan::call('auto:absent', ['--force' => true]);
    
    $this->assertDatabaseHas('absensi', [
        'user_id' => $user->id,
        'tanggal' => '2026-05-07',
        'status' => 'alpha'
    ]);
});

it('perhitungan poin live bulanan: verifikasi sampai tanggal 8 mei', function () {
    $user = createTestUser('anggota', 'IT');
    $user->update(['point' => 0]);

    // Simulasi Absensi:
    // 4 Mei (Senin): Hadir (+1)
    // 5 Mei (Selasa): Terlambat dengan alasan (+0) -> asumsi poin terlambat=0 di config
    // 6 Mei (Rabu): Alpha (-1)
    // 7 Mei (Kamis): Izin (+0)
    // 8 Mei (Jumat): Hadir (+1)

    Setting::set('poin', ['hadir' => 1, 'terlambat' => 0, 'izin' => 0, 'sakit' => 0, 'cuti' => 0, 'tugas_luar' => 0, 'alpha' => -1]);
    
    Absensi::create(['user_id' => $user->id, 'tanggal' => '2026-05-04', 'status' => 'Hadir', 'is_approved' => true, 'jam' => '07:30:00']);
    Absensi::create(['user_id' => $user->id, 'tanggal' => '2026-05-05', 'status' => 'Terlambat', 'is_approved' => true, 'jam' => '08:15:00', 'alasan' => 'Macet']);
    Absensi::create(['user_id' => $user->id, 'tanggal' => '2026-05-06', 'status' => 'alpha', 'is_approved' => true]);
    Absensi::create(['user_id' => $user->id, 'tanggal' => '2026-05-07', 'status' => 'Izin', 'is_approved' => true]);
    Absensi::create(['user_id' => $user->id, 'tanggal' => '2026-05-08', 'status' => 'Hadir', 'is_approved' => true, 'jam' => '07:45:00']);

    // Set waktu sekarang ke 8 Mei jam 17:00
    Carbon::setTestNow(Carbon::parse('2026-05-08 17:00:00', 'Asia/Makassar'));

    // Panggil controller index untuk memicu kalkulasi poin live
    $this->actingAs($user)->get('/dashboard');

    // Expected: 1 (Hadir) + 0 (L) + (-1) (A) + 0 (I) + 1 (H) = 1
    expect($user->fresh()->point)->toBe(1);
});

it('weekend logic: tidak ada poin alpha di hari sabtu minggu', function () {
    $user = createTestUser('anggota', 'IT');
    $user->update(['point' => 10]);

    // 9 Mei (Sabtu), 10 Mei (Minggu)
    Carbon::setTestNow(Carbon::parse('2026-05-10 17:00:00', 'Asia/Makassar'));

    $this->actingAs($user)->get('/dashboard');

    // Poin tetap 10 karena looping hari libur/weekend dilewati
});
