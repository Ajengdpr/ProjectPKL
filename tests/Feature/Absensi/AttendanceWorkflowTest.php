<?php

use App\Models\User;
use App\Models\Absensi;
use App\Models\Setting;
use Carbon\Carbon;

it('workflow lengkap: pengajuan sampai approval', function () {
    // 1. Setup User
    $kadin = createTestUser('kadin', 'IT', 'user');
    $kabid = createTestUser('kabid', 'IT', 'user');
    $anggota = createTestUser('anggota', 'IT', 'user');

    // 2. Anggota mengajukan Izin
    Carbon::setTestNow(Carbon::parse('2026-05-07 07:30:00', 'Asia/Makassar'));
    $this->actingAs($anggota)->post('/absen', [
        'status' => 'Izin',
        'alasan' => 'Urusan Keluarga',
        'berkas' => \Illuminate\Http\UploadedFile::fake()->create('surat.pdf')
    ]);

    $absensi = Absensi::where('user_id', $anggota->id)->latest()->first();
    expect($absensi->status)->toBe('Izin');
    expect($absensi->is_approved)->toBeFalse();

    // 3. Kabid melihat daftar approval (Simulasi akses dashboard/statistik)
    // 4. Kabid menyetujui
    $this->actingAs($kabid)->post("/absen/{$absensi->id}/approve");
    expect($absensi->fresh()->is_approved)->toBeTrue();
});

it('workflow penolakan: anggota ditolak kabid', function () {
    $kabid = createTestUser('kabid', 'Keuangan', 'user');
    $anggota = createTestUser('anggota', 'Keuangan', 'user');

    Carbon::setTestNow(Carbon::parse('2026-05-08 07:45:00', 'Asia/Makassar'));
    $this->actingAs($anggota)->post('/absen', [
        'status' => 'Sakit',
        'alasan' => 'Demam',
        'berkas' => \Illuminate\Http\UploadedFile::fake()->create('catatan_dokter.jpg')
    ]);

    $absensi = Absensi::where('user_id', $anggota->id)->latest()->first();
    
    // Kabid menolak
    $this->actingAs($kabid)->post("/absen/{$absensi->id}/reject");
    expect($absensi->fresh()->is_rejected)->toBeTrue();
    expect($absensi->fresh()->is_approved)->toBeFalse();
});

it('akses otorisasi: kabid tidak bisa approve bidang lain', function () {
    $kabidIT = createTestUser('kabid', 'IT', 'user');
    $anggotaKeuangan = createTestUser('anggota', 'Keuangan', 'user');

    $absensi = Absensi::create([
        'user_id' => $anggotaKeuangan->id,
        'tanggal' => '2026-05-07',
        'status' => 'Izin',
        'is_approved' => false
    ]);

    $response = $this->actingAs($kabidIT)->post("/absen/{$absensi->id}/approve");
    $response->assertSessionHas('err', 'Anda tidak memiliki wewenang untuk menyetujui absensi ini.');
    expect($absensi->fresh()->is_approved)->toBeFalse();
});
