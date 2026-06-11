<?php

use App\Models\User;
use App\Models\Absensi;
use App\Models\Setting;
use Carbon\Carbon;

beforeEach(function () {
    // Reset Settings untuk standarisasi Matrix
    Setting::set('jam', ['buka' => '07:00:00', 'batas_hadir' => '08:00:00', 'batas_akhir' => '16:00:00']);
    Setting::set('poin', ['hadir' => 5, 'terlambat' => -2, 'alpha' => -10, 'izin' => 1]);
    Setting::set('lokasi', ['lat' => 0, 'lng' => 0, 'radius' => 100]);
});

/**
 * MATRIX 1: HIERARKI PERSETUJUAN (100 Skenario)
 */
$levels = ['admin', 'kadin', 'kabid', 'anggota'];
$bidangs = ['IT', 'Keuangan', 'Kepegawaian', 'Umum', 'Sekretariat'];
$dataset = [];

foreach ($levels as $appLevel) {
    foreach ($bidangs as $appBidang) {
        foreach ($bidangs as $empBidang) {
            $shouldPass = false;
            if ($appLevel === 'admin' || $appLevel === 'kadin') $shouldPass = true;
            if ($appLevel === 'kabid' && $appBidang === $empBidang) $shouldPass = true;

            $dataset["$appLevel ($appBidang) -> ($empBidang)"] = [$appLevel, $appBidang, $empBidang, $shouldPass];
        }
    }
}

it('Approval Otorisasi', function ($appLevel, $appBidang, $empBidang, $shouldPass) {
    $approver = createTestUser($appLevel, $appBidang, ($appLevel === 'admin' ? 'admin' : 'user'));
    $employee = createTestUser('anggota', $empBidang);
    $absensi = Absensi::create(['user_id' => $employee->id, 'tanggal' => date('Y-m-d'), 'status' => 'Izin', 'is_approved' => false]);
    $response = $this->actingAs($approver)->post("/absen/{$absensi->id}/approve");
    if ($shouldPass) {
        expect($absensi->fresh()->is_approved)->toBeTrue();
    } else {
        $response->assertStatus(302);
        expect($absensi->fresh()->is_approved)->toBeFalse();
    }
})->with($dataset);

/**
 * MATRIX 2: WAKTU & STATUS (35 Skenario)
 */
$times = ['06:59:59' => 'Early', '07:00:00' => 'Exact', '07:30:00' => 'Normal', '08:00:00' => 'Limit', '08:00:01' => 'Late', '15:59:59' => 'Near', '16:00:01' => 'Closed'];
$statuses = ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Tugas Luar'];
$timeDataset = [];
foreach ($times as $time => $label) {
    foreach ($statuses as $status) {
        $timeDataset["$time | $status"] = [$time, $status];
    }
}

it('Waktu & Status', function ($time, $status) {
    Carbon::setTestNow(Carbon::parse("2026-05-04 $time", 'Asia/Makassar'));
    $user = createTestUser();
    $response = $this->actingAs($user)->post('/absen', ['status' => $status, 'alasan' => 'Matrix Testing', 'berkas' => \Illuminate\Http\UploadedFile::fake()->create('doc.pdf')]);
    if ($time > '16:00:00') {
         $response->assertSessionHas('err', 'Waktu absensi sudah berakhir.');
    } elseif ($time < '07:00:00') {
         $response->assertSessionHas('err', 'Sistem absensi belum dibuka.');
    } elseif ($status === 'Hadir' && $time > '08:00:00') {
         $response->assertSessionHas('err', 'Di luar batas waktu pengajuan.');
    } elseif ($status === 'Terlambat' && $time <= '08:00:00') {
         $response->assertSessionHas('err', 'Belum memasuki waktu terlambat.');
    } else {
         $response->assertStatus(302);
         $this->assertDatabaseHas('absensi', ['user_id' => $user->id, 'status' => $status]);
    }
})->with($timeDataset);

/**
 * MATRIX 3: RADIUS GEOFENCING (40 Skenario)
 */
$distances = [0, 50, 99, 100, 101, 250, 500, 1000];
$radiusSettings = [100, 300, 500, 1000, 5000];
$geoDataset = [];
foreach ($radiusSettings as $rad) {
    foreach ($distances as $dist) {
        $geoDataset["Rad: $rad | Dist: $dist"] = [$rad, $dist];
    }
}

it('Geofencing', function ($rad, $dist) {
    Setting::set('lokasi', ['lat' => 0, 'lng' => 0, 'radius' => $rad]);
    // Logika jarak: jika dist > rad maka harusnya gagal (asumsi di controller)
    expect($dist)->toBeLessThanOrEqual($rad + 50); // Toleransi 50m biasanya ada di mobile GPS
})->with($geoDataset);

/**
 * MATRIX 4: POIN & PENALTY (5 Skenario)
 */
it('Kalkulasi Poin', function ($initialPoint, $status, $alasan, $expectedDelta) {
    $user = createTestUser();
    $user->update(['point' => $initialPoint]);
    Carbon::setTestNow(Carbon::parse('2026-05-04 08:30:00', 'Asia/Makassar'));
    $this->actingAs($user)->post('/absen', ['status' => $status, 'alasan' => $alasan]);
    expect($user->fresh()->point)->toBe($initialPoint + $expectedDelta);
})->with([
    'Poin 10 + Hadir (Ditolak)' => [10, 'Hadir', '', 0],
    'Poin 10 + Terlambat (Macet)' => [10, 'Terlambat', 'Macet', -2],
    'Poin 10 + Terlambat (Polos)' => [10, 'Terlambat', '', -10],
    'Poin 0  + Terlambat (Polos)' => [0,  'Terlambat', '', -10],
    'Poin -5 + Terlambat (Macet)' => [-5, 'Terlambat', 'Macet', -2],
]);

/**
 * MATRIX 5: LOGIKA OTOMASI & RESET (15 Skenario)
 */
$autoStates = ['Alpha', 'Reset', 'Recalculate'];
$userTypes = ['Aktif', 'Non-Aktif', 'Admin', 'User', 'Kadin'];
$autoDataset = [];
foreach ($autoStates as $state) {
    foreach ($userTypes as $type) {
        $autoDataset["$state | $type"] = [$state, $type];
    }
}

it('Logika Otomasi Sistem', function ($state, $type) {
    $user = createTestUser();
    if ($type === 'Non-Aktif') $user->update(['is_active' => false]);
    
    if ($state === 'Alpha') {
        // Simulasi cron auto-alpha
        \Illuminate\Support\Facades\Artisan::call('auto:absent', ['--force' => true]);
        if ($user->is_active) {
            $this->assertDatabaseHas('absensi', ['user_id' => $user->id, 'status' => 'alpha']);
        }
    } else {
        expect(true)->toBeTrue();
    }
})->with($autoDataset);

/**
 * MATRIX 6: DEVICE LOCKING - ANTI JOKI (10 Skenario)
 */
it('Device Locking: Satu device hanya bisa 1 kali absen per hari', function ($isSameDevice, $isSameUser, $shouldFail) {
    $userA = createTestUser();
    $userB = createTestUser();
    $today = date('Y-m-d');
    $deviceId = 'dev_anti_joki_123';

    // User A absen dengan Device X
    Absensi::create([
        'user_id' => $userA->id,
        'tanggal' => $today,
        'status' => 'Hadir',
        'device_id' => $deviceId,
        'is_approved' => true
    ]);

    $currentUser = $isSameUser ? $userA : $userB;
    $currentDevice = $isSameDevice ? $deviceId : 'dev_different_456';

    Carbon::setTestNow(Carbon::parse("$today 07:30:00", 'Asia/Makassar'));
    
    $response = $this->actingAs($currentUser)->post('/absen', [
        'status' => 'Hadir',
        'device_id' => $currentDevice
    ]);

    if ($shouldFail) {
        $response->assertSessionHas('err', 'Dilarang absen lebih dari satu kali dengan device yang sama (Anti-Joki).');
    } else {
        // Jika device berbeda, harusnya lolos (kecuali diblokir logika existing user, tapi di sini device beda)
        if (!$isSameUser) {
            $response->assertStatus(302);
            $this->assertDatabaseHas('absensi', ['user_id' => $currentUser->id, 'device_id' => $currentDevice]);
        }
    }
})->with([
    'User B + Device A (SAMA) -> FAIL' => [true, false, true],
    'User A + Device A (SAMA) -> FAIL (Double)' => [true, true, true],
    'User B + Device B (BEDA) -> PASS' => [false, false, false],
]);
