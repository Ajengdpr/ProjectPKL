@extends('layouts.admin')
@section('title', 'Pengaturan Aplikasi')

@push('head')
<style>
    :root {
        --primary-blue: #0d6efd;
        --border-color: #f1f5f9;
        --bg-light: #f8fafc;
    }

    body {
        background-color: #f1f5f9;
    }

    .settings-container {
        width: 100%;
        margin-top: 2rem;
        padding-bottom: 5rem;
    }

    .header-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .header-section h3 {
        font-weight: 800;
        color: #1e293b;
        letter-spacing: -0.5px;
    }

    .settings-card {
        background: white;
        border-radius: 20px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        padding: 1.75rem;
        margin-bottom: 1.5rem;
    }

    .card-title-group {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border-color);
    }

    .card-title-group i {
        font-size: 1.5rem;
        color: var(--primary-blue);
        background: rgba(13, 110, 253, 0.1);
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }

    .card-title-group h6 {
        font-weight: 700;
        margin: 0;
        color: #334155;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.85rem;
    }

    .form-label {
        font-weight: 600;
        color: #64748b;
        font-size: 0.8rem;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
    }

    .form-control {
        border-radius: 12px;
        padding: 0.6rem 1rem;
        border-color: #e2e8f0;
        background-color: var(--bg-light);
        font-weight: 500;
        transition: all 0.2s;
    }

    .form-control:focus {
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
        background-color: #fff;
    }

    .btn-save-fixed {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        z-index: 1000;
        box-shadow: 0 10px 25px rgba(13, 110, 253, 0.3);
        padding: 0.75rem 2rem;
        border-radius: 50px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .status-info {
        background: #fffbeb;
        border-left: 4px solid #f59e0b;
        padding: 1rem;
        border-radius: 8px;
        margin-top: 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-md-4 settings-container">
    <div class="header-section">
        <div>
            <h3>Pengaturan Konfigurasi</h3>
            <p class="text-muted small mb-0">Kelola parameter sistem absensi, poin, dan waktu kerja.</p>
        </div>
    </div>

    @if(session('ok'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-4 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('ok') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="post" action="{{ route('admin.settings.save') }}">
        @csrf

        {{-- Section 1: Konfigurasi Poin --}}
        <div class="settings-card">
            <div class="card-title-group">
                <i class="bi bi-star-fill"></i>
                <h6>Konfigurasi Poin Kehadiran</h6>
            </div>
            <div class="row g-3">
                @foreach(\App\Models\Absensi::getStatuses() as $key=>$label)
                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label">{{ $label }}</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-3" style="border-radius: 12px 0 0 12px;"><i class="bi bi-plus-slash-minus small"></i></span>
                            <input type="number" class="form-control border-start-0" name="poin[{{ $key }}]" value="{{ $poin[$key] ?? 0 }}" style="border-radius: 0 12px 12px 0;">
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="form-text mt-3 text-muted small">
                <i class="bi bi-info-circle me-1"></i> Nilai poin ini akan otomatis dihitung ke dalam total poin bulanan setiap pegawai.
            </div>
        </div>

        <div class="row g-3">
            {{-- Section 2: Radius Lokasi Kantor --}}
            <div class="col-md-6">
                <div class="settings-card h-100">
                    <div class="card-title-group">
                        <i class="bi bi-geo-alt-fill"></i>
                        <h6>Geofencing Kantor</h6>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Latitude</label>
                        <input type="number" step="any" class="form-control" name="lokasi[lat]" value="{{ $lokasi['lat'] }}" placeholder="-3.xxxx">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Longitude</label>
                        <input type="number" step="any" class="form-control" name="lokasi[lng]" value="{{ $lokasi['lng'] }}" placeholder="114.xxxx">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Radius (Meter)</label>
                        <div class="input-group">
                            <input type="number" class="form-control border-end-0" name="lokasi[radius]" value="{{ $lokasi['radius'] }}">
                            <span class="input-group-text bg-white text-muted" style="border-radius: 0 12px 12px 0;">M</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 3: Pengaturan Waktu --}}
            <div class="col-md-6">
                <div class="settings-card h-100">
                    <div class="card-title-group">
                        <i class="bi bi-clock-fill"></i>
                        <h6>Jam Operasional</h6>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Batas Waktu Hadir (Check-in)</label>
                        <input type="time" step="1" class="form-control" name="jam[batas_hadir]" value="{{ $jam['batas_hadir'] }}">
                        <div class="form-text small">Pegawai dianggap terlambat jika absen setelah jam ini.</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Batas Waktu Absen (Cut-off)</label>
                        <input type="time" step="1" class="form-control" name="jam[batas_akhir]" value="{{ $jam['batas_akhir'] ?? '16:00:00' }}">
                        <div class="form-text small">Sistem akan terkunci otomatis setelah jam ini berakhir.</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 4: Pengaturan Hari Libur --}}
        <div class="settings-card">
            <div class="card-title-group">
                <i class="bi bi-calendar-x-fill"></i>
                <h6>Manajemen Hari Libur & Maintenance</h6>
            </div>
            <div class="mb-4">
                <label class="form-label">Pesan Penonaktifan Sistem</label>
                <input type="text" class="form-control" name="status[reason]" value="{{ $status['reason'] ?? '' }}" placeholder="Contoh: Sistem sedang dinonaktifkan sementara karena libur hari raya.">
                <div class="form-text small">Pesan ini akan muncul di dashboard seluruh pegawai.</div>
            </div>
            
            <div class="mb-2">
                <label class="form-label">Daftar Tanggal Libur Spesifik</label>
                <textarea class="form-control" name="status[hari_libur]" rows="4" placeholder="Format: YYYY-MM-DD (Satu baris satu tanggal)">{{ $status['hari_libur'] ?? '' }}</textarea>
            </div>
            <div class="status-info text-dark small">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Perhatian:</strong> Sistem absensi akan otomatis <strong>TERKUNCI</strong> pada daftar tanggal di atas. Pastikan format penulisan benar (Contoh: 2026-04-25).
            </div>
        </div>

        {{-- Floating Save Button --}}
        <button type="submit" class="btn btn-primary btn-save-fixed shadow">
            <i class="bi bi-save2-fill"></i> Simpan Perubahan
        </button>
    </form>
</div>
@endsection
