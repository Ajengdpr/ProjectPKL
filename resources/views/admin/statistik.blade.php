@extends('layouts.admin')
@section('title', 'Statistik Pegawai')

@push('head')
<style>
    :root {
        --primary-blue: #0d6efd;
        --border-color: #f1f5f9;
        --bg-light: #f8fafc;
        --text-dark: #1e293b;
        --text-muted: #64748b;
        --section-gap: 2rem;
    }

    body {
        background-color: #f1f5f9;
    }

    /* Memastikan lebar penuh seperti halaman manajemen */
    .statistik-page-container {
        width: 100%;
        margin-top: 0;
        padding-bottom: 5rem;
    }

    /* Modern Header (Gradient Lebar) */
    .header-section {
        background: linear-gradient(135deg, #0d6efd 0%, #003d99 100%);
        padding: 2.5rem 3rem;
        border-radius: 24px;
        border: none;
        box-shadow: 0 10px 25px rgba(0, 61, 153, 0.15);
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--section-gap);
        color: white;
    }

    .header-content h3 {
        font-weight: 800;
        margin: 0;
        color: white;
        letter-spacing: -0.5px;
    }

    .header-content p {
        margin: 0;
        color: rgba(255, 255, 255, 0.8);
        font-weight: 500;
    }

    /* Toolbar Card Filter */
    .toolbar-card {
        background: white;
        border-radius: 20px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        padding: 1.5rem;
        margin-bottom: var(--section-gap);
    }

    .form-label {
        font-weight: 700;
        color: var(--text-muted);
        font-size: 0.75rem;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
    }

    .form-control, .form-select {
        border-radius: 12px;
        padding: 0.6rem 1rem;
        border-color: #e2e8f0;
        background-color: var(--bg-light);
        font-weight: 500;
        font-size: 0.9rem;
    }

    /* Statistik Content Styling */
    .stat-card {
        background: white;
        border-radius: 24px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
        padding: 1.5rem;
        margin-bottom: var(--section-gap);
    }

    /* User Badge */
    .user-pill {
        background: white;
        color: var(--primary-blue);
        padding: 0.5rem 1.25rem;
        border-radius: 50px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .user-pill img {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
    }

    /* Table Calendar */
    .table-calendar { border-collapse: separate; border-spacing: 4px; }
    .table-calendar td { border: 1px solid #f0f0f0 !important; border-radius: 8px; overflow: hidden; }
    .border-bottom-dotted { border-bottom: 1px dotted #dee2e6; }
</style>
@endpush

@section('content')
@php
use Carbon\Carbon;

$tz = 'Asia/Makassar';
$hariDalamBulan = Carbon::parse($bulan.'-01')->daysInMonth;
$now = Carbon::now($tz);

if(Carbon::parse($bulan.'-01')->format('Y-m') < $now->format('Y-m')){
    $maxHari = $hariDalamBulan;
} elseif(Carbon::parse($bulan.'-01')->format('Y-m') == $now->format('Y-m')){
    $maxHari = $now->day;
} else {
    $maxHari = 0;
}

$poinKeyMap = [
    'Hadir'            => 'hadir',
    'Terlambat'        => 'terlambat',
    'Izin'             => 'izin',
    'Sakit'            => 'sakit',
    'Cuti'             => 'cuti',
    'Tugas Luar'       => 'tugas_luar',
    'Tanpa Keterangan' => 'alpha',
];
@endphp

<div class="container-fluid px-md-4 statistik-page-container">
    {{-- Header Section --}}
    <div class="header-section">
        <div class="header-content">
            <h3>Statistik Pegawai</h3>
            <p>Analisis poin dan riwayat absensi bulanan pegawai secara realtime.</p>
        </div>
        @if($selectedUser)
            <div class="user-pill d-none d-md-flex shadow-sm">
                @php $foto = $selectedUser->foto ? asset('storage/'.$selectedUser->foto).'?v='.time() : asset('img/default-avatar.jpg'); @endphp
                <img src="{{ $foto }}" alt="Avatar" onerror="this.src='{{ asset('img/default-avatar.jpg') }}'">
                <span>{{ $selectedUser->nama }}</span>
            </div>
        @endif
    </div>

    {{-- Toolbar Filter --}}
    <div class="toolbar-card">
        <form method="get">
            <div class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label class="form-label">Pilih Pegawai</label>
                    <select name="user_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Pilih Pegawai --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->nama }} ({{ $u->bidang ?: 'Umum' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Pilih Bulan</label>
                    <input type="month" name="bulan" value="{{ $bulan }}" class="form-control" onchange="this.form.submit()">
                </div>
            </div>
        </form>
    </div>

    @if(!$selectedUser)
        <div class="stat-card text-center py-5">
            <div class="text-muted opacity-50 mb-3"><i class="bi bi-search fs-1"></i></div>
            <h5 class="fw-bold text-muted">Pilih nama pegawai untuk memuat data statistik</h5>
            <p class="text-muted small">Laporan akan ditampilkan setelah Anda memilih pegawai di atas.</p>
        </div>
    @else
        <div class="row g-4">
            {{-- Statistik Poin --}}
            <div class="col-lg-5 col-md-6">
                <div class="stat-card h-100">
                    <h6 class="fw-bold mb-4 text-secondary text-uppercase small" style="letter-spacing: 1px;">Analisis Poin</h6>
                    
                    <div class="text-center mb-4">
                        <div style="position: relative; width: 160px; height: 160px; margin: 0 auto;">
                            <canvas id="pieChart"></canvas>
                            <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;">
                                <div class="fw-bold text-dark fs-4 lh-1">{{ $totalPoin }}</div>
                                <div class="text-muted small" style="font-size: 0.6rem;">POIN</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        @foreach($statusColors as $s=>$color)
                            @php
                                $key = $poinKeyMap[$s] ?? null;
                                $poin = $key ? ($poinConfig[$key] ?? 0) : 0;
                                $class = ($poin > 0) ? 'text-success' : (($poin < 0) ? 'text-danger' : 'text-secondary');
                            @endphp
                            <div class="d-flex align-items-center justify-content-between mb-1 pb-1 border-bottom-dotted">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle me-2" style="width:8px; height:8px; background-color: {{ $color }};"></div>
                                    <span class="text-muted" style="font-size: 0.75rem;">{{ $s }}</span>
                                </div>
                                <span class="fw-bold {{ $class }}" style="font-size: 0.75rem;">@if($poin > 0)+@endif{{ $poin }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Log Kalender --}}
            <div class="col-lg-7 col-md-6">
                <div class="stat-card h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold mb-0 text-secondary text-uppercase small" style="letter-spacing: 1px;">Log Kehadiran Bulanan</h6>
                        <a href="#" id="btnExportCsvAdmin" class="btn btn-outline-success btn-sm px-3 rounded-pill fw-bold">
                            <i class="bi bi-download me-1"></i> Export CSV
                        </a>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-calendar mb-0">
                            <thead class="bg-light text-center small">
                                <tr>
                                    <th class="py-2 text-muted fw-bold" style="width: 20%;">SEN</th>
                                    <th class="py-2 text-muted fw-bold" style="width: 20%;">SEL</th>
                                    <th class="py-2 text-muted fw-bold" style="width: 20%;">RAB</th>
                                    <th class="py-2 text-muted fw-bold" style="width: 20%;">KAM</th>
                                    <th class="py-2 text-muted fw-bold" style="width: 20%;">JUM</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $startDate = Carbon::parse($bulan.'-01');
                                    $firstDayPadding = $startDate->dayOfWeekIso - 1;
                                @endphp
                                <tr>
                                @for ($i = 0; $i < $firstDayPadding; $i++)
                                    <td class="bg-light-subtle"></td>
                                @endfor

                                @for ($day = 1; $day <= $hariDalamBulan; $day++)
                                    @php
                                        $currentDate = Carbon::parse($bulan.'-'.$day);
                                        if ($currentDate->dayOfWeekIso == 1 && $day > 1) { echo '</tr><tr>'; }
                                    @endphp

                                    @if (!$currentDate->isWeekend())
                                        @php
                                            $tanggalString = $currentDate->toDateString();
                                            $absen = $absensi->firstWhere('tanggal', $tanggalString);
                                            
                                            $statusConfig = \App\Models\Setting::get('status', ['hari_libur' => '']);
                                            $hariLibur = explode("\n", str_replace("\r", "", $statusConfig['hari_libur'] ?? ''));
                                            $isHariLibur = in_array($tanggalString, $hariLibur);

                                            $jamConfig = array_merge(['batas_hadir' => '08:00:00'], \App\Models\Setting::get('jam', []));

                                            $status = '';
                                            $isPending = false;
                                            if ($isHariLibur) {
                                                $status = 'LIBUR';
                                            } elseif ($day <= $maxHari) {
                                                if ($absen) { 
                                                    if ($absen->is_rejected) {
                                                        $status = 'Tanpa Keterangan';
                                                    } else {
                                                        $status = $absen->status;
                                                        $isPending = !$absen->is_approved;
                                                    }
                                                } else {
                                                    $isTodayBeforeAlpha = $currentDate->isToday() && (Carbon::now($tz)->format('H:i:s') <= $jamConfig['batas_hadir']);
                                                    if (!$isTodayBeforeAlpha) { 
                                                        $status = 'Tanpa Keterangan'; 
                                                    }
                                                }
                                            }
                                            
                                            $bgColor = '#ffffff';
                                            $borderColor = 'transparent';
                                            $textColor = 'text-dark';

                                            if($status === 'LIBUR') {
                                                $bgColor = '#fff1f2'; $borderColor = '#f43f5e'; $textColor = 'text-danger';
                                            } elseif($status) {
                                                $rawColor = $statusColors[$status] ?? '#f8f9fa';
                                                $bgColor = $isPending ? '#f1f5f9' : ($rawColor . '22'); 
                                                $borderColor = $isPending ? '#94a3b8' : ($statusColors[$status] ?? 'transparent');
                                            }
                                        @endphp
                                        <td class="p-0 position-relative" style="height: 75px;">
                                            <div class="h-100 p-2 d-flex flex-column" style="background-color: {{ $bgColor }}; border-left: 3px solid {{ $borderColor }};">
                                                <div class="fw-bold {{ $currentDate->isToday() ? 'text-primary' : $textColor }}" style="font-size: 0.85rem;">
                                                    {{ $day }}
                                                </div>
                                                @if($status)
                                                    <div class="mt-auto">
                                                        <span class="badge p-0 {{ $status === 'LIBUR' ? 'text-danger fw-bold' : ($isPending ? 'text-muted fw-normal' : 'text-dark fw-medium') }}" style="font-size: 0.6rem; text-wrap: balance;">
                                                            {{ $status }} @if($isPending) <i class="bi bi-clock-history" title="Menunggu Persetujuan"></i> @endif
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    @endif
                                @endfor

                                @php
                                    $lastDayOfMonth = Carbon::parse($bulan.'-'.$hariDalamBulan);
                                    if ($lastDayOfMonth->dayOfWeekIso < 5) {
                                        $lastDayPadding = 5 - $lastDayOfMonth->dayOfWeekIso;
                                        for ($i = 0; $i < $lastDayPadding; $i++) { echo '<td class="bg-light-subtle"></td>'; }
                                    }
                                @endphp
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
@if($selectedUser)
const ctx = document.getElementById('pieChart').getContext('2d');
const pieData = {
    labels: ['Hadir','Izin','Cuti','Sakit','Terlambat','Tugas Luar','Tanpa Keterangan'],
    datasets:[{
        data:[
            {{ $rekapData['Hadir'] }},
            {{ $rekapData['Izin'] }},
            {{ $rekapData['Cuti'] }},
            {{ $rekapData['Sakit'] }},
            {{ $rekapData['Terlambat'] }},
            {{ $rekapData['Tugas Luar'] }},
            {{ $rekapData['Tanpa Keterangan'] }}
        ],
        backgroundColor:['#36A2EB','#FFCE56','#9966FF','#FF6384','#4BC0C0','#FF9F40','#e0e0e0'],
        borderWidth:2,
        borderColor: '#ffffff'
    }]
};
if(!{{ $adaData ? 'true':'false' }}){
    pieData.datasets[0].data=[1];
    pieData.labels=['Tidak ada data'];
    pieData.datasets[0].backgroundColor=['#e0e0e0'];
}
new Chart(ctx,{type:'doughnut',data:pieData,options:{responsive:true,cutout:'75%',plugins:{legend:{display:false}}}});

document.getElementById('btnExportCsvAdmin')?.addEventListener('click', function(e){
    e.preventDefault();
    const bulan = document.querySelector('input[name="bulan"]').value;
    const userId = "{{ $selectedUser->id }}";
    window.location.href = "{{ route('admin.statistik.export') }}?bulan=" + bulan + "&user_id=" + userId;
});
@endif
</script>
@endpush
@endsection
