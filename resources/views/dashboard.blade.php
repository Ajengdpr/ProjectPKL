@extends('layouts.app')
@section('title', 'Dashboard')

@push('head')
<style>
    :root {
        --primary-blue: #2563eb;
        --primary-soft: #eff6ff;
        --success-soft: #ecfdf5;
        --warning-soft: #fffbeb;
        --danger-soft: #fef2f2;
        --info-soft: #f0f9ff;
        --border-color: #f1f5f9;
        --text-main: #0f172a;
        --text-muted: #64748b;
        --section-gap: 1.25rem;
    }

    body {
        background-color: #f8fafc;
        color: var(--text-main);
    }

    .dashboard-container {
        padding-top: 1rem;
        padding-bottom: 5rem;
    }

    /* Modern Card Base - Extra Compact */
    .premium-card {
        background: white;
        border-radius: 18px;
        border: 1px solid var(--border-color);
        box-shadow: 0 10px 40px -10px rgba(0,0,0,0.04);
        padding: 0.75rem 1.25rem;
        transition: all 0.3s ease;
    }

    /* Hero Section */
    .hero-wrapper {
        display: grid;
        grid-template-columns: 1.6fr 1fr;
        gap: 1rem;
        margin-bottom: var(--section-gap);
    }

    .welcome-card {
        border-left: 5px solid var(--primary-blue);
        background: linear-gradient(to right, #ffffff, #f9fbff);
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .user-avatar-modern {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        object-fit: cover;
        border: 3px solid white;
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    }

    .greeting-text h2 {
        font-weight: 800;
        letter-spacing: -1px;
        color: var(--text-main);
        font-size: 1.4rem;
        margin-bottom: 0;
    }

    .ux-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.15rem 0.6rem;
        background: var(--primary-blue);
        color: white;
        border-radius: 50px;
        font-size: 0.6rem;
        font-weight: 700;
        text-transform: uppercase;
        margin-top: 0.25rem;
    }

    .ux-message {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--primary-blue);
        background: rgba(37, 99, 235, 0.08);
        padding: 0.15rem 0.5rem;
        border-radius: 6px;
        display: inline-block;
        margin-top: 0.25rem;
    }

    /* Clock Widget - Locked Position */
    .clock-widget {
        text-align: right;
        min-width: 140px;
    }

    #realtime-clock {
        font-size: 2.25rem;
        font-weight: 800;
        color: var(--primary-blue);
        letter-spacing: -1px;
        line-height: 1;
    }

    .hero-date {
        color: var(--text-muted);
        font-weight: 600;
        font-size: 0.75rem;
    }

    /* Mini Widgets */
    .widget-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
        margin-top: 0.75rem;
    }

    .mini-widget {
        padding: 0.5rem 0.75rem;
        border-radius: 14px;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        border: 1px solid rgba(0,0,0,0.02);
    }

    .w-blue { background: #eff6ff; color: #1e40af; }
    .w-emerald { background: #ecfdf5; color: #065f46; }

    .widget-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        background: white;
        box-shadow: 0 4px 8px rgba(0,0,0,0.04);
    }

    /* Location Panel */
    .location-panel {
        background: #f0f7ff;
        border: 1px solid #e0e7ff;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .loc-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.75rem;
    }

    .loc-data-card {
        background: white;
        padding: 0.65rem;
        border-radius: 16px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.02);
        text-align: center;
    }

    /* Menu Absensi Section */
    .menu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: 1rem;
        margin-bottom: var(--section-gap);
    }

    .menu-item {
        background: #f0f7ff;
        border: 1px solid #e0e7ff;
        border-radius: 20px;
        padding: 1.25rem 0.75rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        text-decoration: none !important;
        color: var(--text-dark);
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
    }

    .menu-item:hover:not(.disabled) {
        transform: translateY(-8px);
        background: white;
        border-color: var(--primary-blue);
        box-shadow: 0 15px 30px rgba(13, 110, 253, 0.1);
    }

    .menu-item.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background: #f8fafc;
        filter: grayscale(1);
    }

    .m-icon-box {
        width: 52px;
        height: 52px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        background: white;
        box-shadow: 0 6px 12px rgba(0,0,0,0.05);
        transition: all 0.3s;
    }

    .mi-hadir { color: #2563eb; }
    .mi-izin { color: #f59e0b; }
    .mi-sakit { color: #ef4444; }
    .mi-tugas { color: #10b981; }
    .mi-cuti { color: #8b5cf6; }
    .mi-telat { color: #64748b; }

    .menu-item:hover:not(.disabled) .m-icon-box {
        transform: scale(1.1) rotate(5deg);
    }

    .menu-item span {
        font-weight: 800;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Section Headers */
    .section-title {
        font-weight: 800;
        font-size: 1.1rem;
        margin-bottom: 1rem;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .section-title::before {
        content: "";
        width: 4px;
        height: 20px;
        background: var(--primary-blue);
        border-radius: 10px;
    }

    .table-modern thead th {
        background: #f8fafc;
        color: var(--text-dark);
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 0.85rem;
        font-weight: 700;
        border: none;
    }

    .table-modern tbody td, .table-modern tfoot td {
        padding: 0.85rem;
        border-bottom: 1px solid #f1f5f9;
        font-weight: 400;
        color: var(--text-dark);
        font-size: 0.8rem;
    }

    .table-modern tfoot tr {
        font-weight: 700;
        background: #f8fafc;
    }

    .table-modern tbody td.bidang-name {
        font-weight: 700;
    }

    /* Modal */
    .modal-content { border-radius: 28px; border: none; overflow: hidden; }
    .modal-header { background: var(--primary-blue); color: white; padding: 1.5rem; border: none; }
    .btn-close-white { filter: brightness(0) invert(1); }

    /* Responsive Adjustments */
    @media (max-width: 992px) {
        .hero-wrapper { grid-template-columns: 1fr; gap: 1rem; }
        .clock-widget { text-align: left; margin-top: 1rem; }
    }

    @media (max-width: 768px) {
        .dashboard-container { padding-top: 0.5rem; }
        .premium-card { padding: 0.75rem 1rem; }
        .greeting-text h2 { font-size: 1.3rem; }
        #realtime-clock { font-size: 2rem; }
        .widget-grid { grid-template-columns: 1fr; gap: 0.5rem; }
        .menu-grid { grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-md-4 dashboard-container">
    <div id="custom-alert-container" style="position: fixed; top: 20px; right: 20px; z-index: 2000; max-width: 350px;"></div>

    {{-- 1. Hero Section --}}
    <div class="hero-wrapper">
        <div class="premium-card welcome-card">
            <div class="d-flex justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    @php $avatar = $user->foto ? asset('storage/'.$user->foto) : asset('img/default-avatar.jpg'); @endphp
                    <img src="{{ $avatar }}" class="user-avatar-modern" onerror="this.src='{{ asset('img/default-avatar.jpg') }}'">
                    <div class="greeting-text">
                        <h5 class="text-muted mb-0 small">Selamat {{ \Carbon\Carbon::now()->hour < 12 ? 'Pagi' : (\Carbon\Carbon::now()->hour < 15 ? 'Siang' : (\Carbon\Carbon::now()->hour < 18 ? 'Sore' : 'Malam')) }}</h5>
                        <h2>{{ \Illuminate\Support\Str::title($user->nama) }}</h2>
                        
                        <div class="d-flex flex-wrap gap-2">
                            <div class="ux-status-pill">
                                @if(!($isAbsensiActive ?? true))
                                    <i class="bi bi-calendar-x-fill"></i> <span>Libur Hari Ini</span>
                                @elseif($isPending ?? false)
                                    <i class="bi bi-hourglass-split"></i> <span>Menunggu Persetujuan</span>
                                @else
                                    <i class="bi {{ $sudahAbsenToday ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                    <span>{{ $sudahAbsenToday ? 'Presensi Selesai' : 'Belum Presensi' }}</span>
                                @endif
                            </div>
                            @if(!($isAbsensiActive ?? true))
                            <div class="ux-message">
                                <i class="bi bi-info-circle-fill me-1"></i> {{ $disableReason ?? 'Sistem presensi dinonaktifkan.' }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="clock-widget d-none d-sm-block">
                    <div id="realtime-clock">00:00:00</div>
                    <div class="hero-date">{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</div>
                </div>
            </div>
            
            <div class="widget-grid">
                <div class="mini-widget w-blue">
                    <div class="widget-icon text-primary"><i class="bi bi-stars"></i></div>
                    <div>
                        <div class="small fw-bold opacity-75" style="font-size: 0.55rem;">POIN SAYA</div>
                        <div class="fw-bold">{{ $user->point ?? 0 }}</div>
                    </div>
                </div>
                <div class="mini-widget w-emerald">
                    <div class="widget-icon text-success"><i class="bi bi-building"></i></div>
                    <div>
                        <div class="small fw-bold opacity-75" style="font-size: 0.55rem;">BIDANG</div>
                        <div class="fw-bold small">{{ strtoupper($user->bidang ?? 'Umum') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="premium-card location-panel text-center">
            <div class="loc-header justify-content-center">
                <div class="d-flex align-items-center gap-2">
                    <div class="widget-icon text-primary"><i class="bi bi-geo-alt-fill"></i></div>
                    <span class="fw-bold text-dark small">Verifikasi Lokasi</span>
                </div>
            </div>
            <div class="mb-3">
                <div id="geo-status" class="fw-bold fs-5 text-dark">Mendeteksi...</div>
            </div>
            <div class="row g-2">
                <div class="col-6">
                    <div class="loc-data-card">
                        <div class="small text-muted fw-bold mb-1" style="font-size: 0.5rem;">JARAK</div>
                        <div id="geo-distance" class="fw-bold text-primary small">-</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="loc-data-card">
                        <div class="small text-muted fw-bold mb-1" style="font-size: 0.5rem;">AKURASI</div>
                        <div id="geo-accuracy" class="fw-bold text-primary small">-</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Mobile Clock (Only visible on very small screens) --}}
    <div class="d-sm-none text-center mb-4">
        <div id="realtime-clock-mobile" class="fw-bold text-primary" style="font-size: 2.5rem; line-height:1;">00:00:00</div>
        <div class="small text-muted fw-bold">{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</div>
    </div>

    {{-- 2. Attendance Menu Section --}}
    <h5 class="section-title">Menu Utama Presensi</h5>
    <div class="menu-grid">
        @php
            $actions = [
                ['id' => 'btnHadir', 'title' => 'HADIR', 'icon' => 'bi-person-check-fill', 'class' => 'mi-hadir', 'status' => 'Hadir', 'expired' => ($isBeforeBuka ?? false) || ($isPastBatasHadir ?? false)],
                ['id' => 'btnIzin', 'title' => 'IZIN', 'icon' => 'bi-file-earmark-text-fill', 'class' => 'mi-izin', 'status' => 'Izin', 'expired' => ($isBeforeBuka ?? false) || ($isPastBatasHadir ?? false)],
                ['id' => 'btnSakit', 'title' => 'SAKIT', 'icon' => 'bi-heart-pulse-fill', 'class' => 'mi-sakit', 'status' => 'Sakit', 'expired' => ($isBeforeBuka ?? false) || ($isPastBatasHadir ?? false)],
                ['id' => 'btnTugasLuar', 'title' => 'TUGAS LUAR', 'icon' => 'bi-briefcase-fill', 'class' => 'mi-tugas', 'status' => 'Tugas Luar', 'expired' => ($isBeforeBuka ?? false) || ($isPastBatasHadir ?? false)],
                ['id' => 'btnCuti', 'title' => 'CUTI', 'icon' => 'bi-calendar-x-fill', 'class' => 'mi-cuti', 'status' => 'Cuti', 'expired' => ($isBeforeBuka ?? false) || ($isPastBatasHadir ?? false)],
                ['id' => 'btnTerlambat', 'title' => 'TERLAMBAT', 'icon' => 'bi-alarm-fill', 'class' => 'mi-telat', 'status' => 'Terlambat', 'expired' => ($isBeforeBuka ?? false) || ($isBeforeBatasHadir ?? false) || ($isPastBatasAkhir ?? false)],
            ];
        @endphp
        @foreach($actions as $act)
            @php $absenLocked = ($sudahAbsenToday ?? false) || !($isAbsensiActive ?? true); @endphp
            <div id="{{ $act['id'] }}" class="menu-item {{ $absenLocked ? 'disabled' : '' }}"
               @if(!$absenLocked)
                 @if($act['expired']) 
                    @if($act['status'] === 'Terlambat')
                        @if($isBeforeBuka ?? false)
                            onclick="showCustomAlert('Sistem absensi belum dibuka', 'warning')"
                        @elseif($isBeforeBatasHadir ?? false)
                            onclick="showCustomAlert('Belum memasuki waktu terlambat', 'warning')"
                        @else
                            onclick="showCustomAlert('Waktu presensi sudah berakhir', 'warning')"
                        @endif
                    @else
                        @if($isBeforeBuka ?? false)
                            onclick="showCustomAlert('Sistem absensi belum dibuka', 'warning')"
                        @else
                            onclick="showCustomAlert('Di luar batas waktu pengajuan', 'warning')"
                        @endif
                    @endif
                 @else data-bs-toggle="modal" data-bs-target="#absenModal" data-status="{{ $act['status'] }}" @endif
               @endif>
                <div class="m-icon-box {{ $act['class'] }}"><i class="bi {{ $act['icon'] }}"></i></div>
                <span>{{ $act['title'] }}</span>
            </div>
        @endforeach
    </div>

    {{-- 3. Recap Table Section --}}
    <h5 class="section-title">Rekap Absensi Per Bidang</h5>
    <div class="rekap-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 table-modern" id="table-rekap">
                <thead>
                    <tr>
                        <th class="ps-4">Bidang</th>
                        <th class="text-center">Staf</th>
                        <th class="text-center">Hadir</th>
                        <th class="text-center">Cuti</th>
                        <th class="text-center">Sakit</th>
                        <th class="text-center">TL</th>
                        <th class="text-center">Terlambat</th>
                        <th class="text-center">Izin</th>
                        <th class="text-center pe-4">TK</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($daftarBidang as $b)
                        @php $r = $rekapPerBidang[$b->bidang] ?? null; @endphp
                        <tr>
                            <td class="ps-4 bidang-name">{{ $b->bidang }}</td>
                            <td class="text-center"><span class="badge bg-light text-dark rounded-pill px-3" style="font-size: 0.8rem; font-weight: 600;">{{ $b->jumlah_pegawai }}</span></td>
                            <td class="text-center">{{ $r->hadir ?? 0 }}</td>
                            <td class="text-center">{{ $r->cuti ?? 0 }}</td>
                            <td class="text-center">{{ $r->sakit ?? 0 }}</td>
                            <td class="text-center">{{ $r->tugas_luar ?? 0 }}</td>
                            <td class="text-center">{{ $r->terlambat ?? 0 }}</td>
                            <td class="text-center">{{ $r->izin ?? 0 }}</td>
                            <td class="text-center pe-4 text-danger fw-bold">{{ $r->alpha ?? 0 }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-light-subtle">
                    <tr class="fw-bold">
                        <td class="ps-4 text-dark text-uppercase">TOTAL KESELURUHAN</td>
                        <td class="text-center text-dark">{{ $daftarBidang->sum('jumlah_pegawai') }}</td>
                        <td class="text-center">{{ $rekapPerBidang->sum(fn($rekap) => $rekap->hadir) }}</td>
                        <td class="text-center">{{ $rekapPerBidang->sum(fn($rekap) => $rekap->cuti) }}</td>
                        <td class="text-center">{{ $rekapPerBidang->sum(fn($rekap) => $rekap->sakit) }}</td>
                        <td class="text-center">{{ $rekapPerBidang->sum(fn($rekap) => $rekap->tugas_luar) }}</td>
                        <td class="text-center">{{ $rekapPerBidang->sum(fn($rekap) => $rekap->terlambat) }}</td>
                        <td class="text-center">{{ $rekapPerBidang->sum(fn($rekap) => $rekap->izin) }}</td>
                        <td class="text-center pe-4 text-danger">{{ $rekapPerBidang->sum(fn($rekap) => $rekap->alpha) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- Absen Modal --}}
<div class="modal fade" id="absenModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <form method="POST" action="{{ route('absen.store') }}" onsubmit="return lockSubmit(this)" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="fw-bold mb-0">Input Presensi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="statusField" name="status" value="Hadir">
                    <div class="mb-4">
                        <label class="form-label small text-muted fw-bold text-uppercase">Status Terpilih</label>
                        <input class="form-control form-control-lg bg-light border-0 fw-bold text-primary" id="statusPreview" value="Hadir" disabled>
                    </div>
                    <div class="mb-4" id="alasanWrapper">
                        <label class="form-label small text-muted fw-bold text-uppercase" id="alasanLabel">Keterangan / Alasan</label>
                        <textarea class="form-control border-0 bg-light" id="alasanInput" name="alasan" rows="3" placeholder="Masukkan keterangan tambahan..."></textarea>
                    </div>
                    <div id="fileUploadContainer" style="display: none;">
                        <label class="form-label small text-muted fw-bold text-uppercase" id="fileLabel">Upload Lampiran (PDF/Gambar)</label>
                        <input class="form-control border-0 bg-light" type="file" id="fileInput" name="berkas">
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal" type="button">Batal</button>
                    <button class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm" id="submitBtn" type="submit">
                        <span class="btn-text">Kirim Sekarang</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function updateClock() {
        const now = new Date();
        const opts = { timeZone: 'Asia/Makassar', hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit' };
        const timeStr = new Intl.DateTimeFormat('id-ID', opts).format(now);
        const clockEl = document.getElementById('realtime-clock');
        const clockMobileEl = document.getElementById('realtime-clock-mobile');
        if (clockEl) clockEl.textContent = timeStr;
        if (clockMobileEl) clockMobileEl.textContent = timeStr;
    }
    setInterval(updateClock, 1000);
    updateClock();

    function showCustomAlert(message, type = 'danger') {
        const container = document.getElementById('custom-alert-container');
        if (!container) return;
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-dark alert-dismissible fade show border-0 shadow-lg rounded-4 p-3 mb-2`;
        alertDiv.innerHTML = `<strong><i class="bi bi-info-circle me-2"></i></strong> ${message}<button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>`;
        container.appendChild(alertDiv);
        setTimeout(() => bootstrap.Alert.getOrCreateInstance(alertDiv)?.close(), 4000);
    }

    const officeLat = {{ $office['lat'] }};
    const officeLng = {{ $office['lng'] }};
    const officeRadius = {{ $office['radius'] }};
    let insideOffice = false;

    function updateLocationUI(statusText, distance, accuracy) {
        const s = document.getElementById('geo-status');
        const d = document.getElementById('geo-distance');
        const a = document.getElementById('geo-accuracy');

        if (s) {
            s.textContent = statusText;
            s.className = `fw-bold fs-4 ${statusText === 'Di Dalam Kantor' ? 'text-success' : 'text-danger'}`;
        }
        if (d) d.textContent = distance !== null ? `${Math.round(distance)}m` : '-';
        if (a) a.textContent = accuracy !== null ? `±${Math.round(accuracy)}m` : '-';
    }

    function handlePos(pos) {
        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;
        const acc = pos.coords.accuracy;
        const dist = (function(lat1, lon1, lat2, lon2) {
            const R = 6371000;
            const toRad = d => d * Math.PI / 180;
            const dLat = toRad(lat2 - lat1);
            const dLon = toRad(lon2 - lon1);
            const a = Math.sin(dLat/2)**2 + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon/2)**2;
            return 2 * R * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        })(lat, lng, officeLat, officeLng);
        insideOffice = (dist <= officeRadius + acc);
        updateLocationUI(insideOffice ? 'Di Dalam Kantor' : 'Di Luar Kantor', dist, acc);
    }

    if (navigator.geolocation) {
        navigator.geolocation.watchPosition(handlePos, (err) => {
            updateLocationUI('Gagal Lokasi', null, null);
        }, { enableHighAccuracy: true });
    }

    function setStatus(s){
        const field = document.getElementById('statusField');
        const preview = document.getElementById('statusPreview');
        const alasan = document.getElementById('alasanInput');
        const wrapper = document.getElementById('alasanWrapper');
        const fileContainer = document.getElementById('fileUploadContainer');
        const fileInput = document.getElementById('fileInput');
        const fileLabel = document.getElementById('fileLabel');
        
        field.value = s;
        preview.value = s;
        fileContainer.style.display = 'none';
        fileInput.removeAttribute('required');
        fileLabel.innerHTML = 'Upload Lampiran (PDF/Gambar)';
        
        if (s === 'Hadir') {
            alasan.removeAttribute('required');
            wrapper.style.display = 'none';
        } else {
            wrapper.style.display = 'block';
            if (['Terlambat', 'Izin'].includes(s)) alasan.setAttribute('required', 'required');
            else alasan.removeAttribute('required');
            
            // WAJIB UPLOAD UNTUK: Izin, Sakit, Tugas Luar, Cuti
            if (['Izin', 'Sakit', 'Tugas Luar', 'Cuti'].includes(s)) {
                fileContainer.style.display = 'block';
                fileInput.setAttribute('required', 'required');
                fileLabel.innerHTML = 'Upload Lampiran (Wajib)';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const modalEl = document.getElementById('absenModal');
        if (!modalEl) return;
        modalEl.addEventListener('show.bs.modal', function (event) {
            const status = event.relatedTarget.getAttribute('data-status');
            if (status) setStatus(status);
            if (['Hadir', 'Terlambat'].includes(status) && !insideOffice) {
                event.preventDefault();
                showCustomAlert('Anda harus berada di dalam jangkauan area kantor.');
            }
        });
    });

    function lockSubmit(form){
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.querySelector('.btn-text').classList.add('d-none');
        btn.querySelector('.spinner-border').classList.remove('d-none');
        return true;
    }

    const todayStr = new Date().toLocaleDateString('en-CA');
    database.ref('rekap/' + todayStr).on('value', (snapshot) => {
        const data = snapshot.val();
        if (!data) return;
        const tbody = document.querySelector('#table-rekap tbody');
        if (!tbody) return;
        let totals = { h:0, c:0, s:0, tl:0, t:0, i:0, a:0 };
        tbody.querySelectorAll('tr').forEach(row => {
            const bidang = row.cells[0].textContent.trim();
            const r = data[bidang] || {};
            row.cells[2].textContent = r.hadir ?? 0;
            row.cells[3].textContent = r.cuti ?? 0;
            row.cells[4].textContent = r.sakit ?? 0;
            row.cells[5].textContent = r.tugas_luar ?? 0;
            row.cells[6].textContent = r.terlambat ?? 0;
            row.cells[7].textContent = r.izin ?? 0;
            row.cells[8].textContent = r.alpha ?? 0;
            totals.h += parseInt(r.hadir ?? 0);
            totals.c += parseInt(r.cuti ?? 0);
            totals.s += parseInt(r.sakit ?? 0);
            totals.tl += parseInt(r.tugas_luar ?? 0);
            totals.t += parseInt(r.terlambat ?? 0);
            totals.i += parseInt(r.izin ?? 0);
            totals.a += parseInt(r.alpha ?? 0);
        });
        const tfoot = document.querySelector('#table-rekap tfoot tr');
        if (tfoot) {
            tfoot.cells[2].textContent = totals.h;
            tfoot.cells[3].textContent = totals.c;
            tfoot.cells[4].textContent = totals.s;
            tfoot.cells[5].textContent = totals.tl;
            tfoot.cells[6].textContent = totals.t;
            tfoot.cells[7].textContent = totals.i;
            tfoot.cells[8].textContent = totals.a;
        }
    });
</script>
@endpush
@endsection
