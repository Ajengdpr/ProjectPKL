@extends('layouts.app')
@section('title','Statistik Absensi')

@section('content')
@php
use Carbon\Carbon;
$tz = config('app.timezone', 'Asia/Makassar');
$hariDalamBulan = Carbon::parse($bulan.'-01')->daysInMonth;
@endphp

<style>
    :root {
        --primary-blue: #0d6efd;
        --border-color: #f1f5f9;
        --bg-light: #f8fafc;
        --section-gap: 2rem;
    }

    body { background-color: #f1f5f9; }

    .header-section {
        background: linear-gradient(135deg, #0d6efd 0%, #003d99 100%);
        padding: 1.5rem 2rem;
        border-radius: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.25rem;
        color: white;
        box-shadow: 0 10px 25px rgba(0, 61, 153, 0.1);
    }

    .header-content h3 { font-weight: 800; margin: 0; font-size: 1.5rem; letter-spacing: -0.5px; }
    .header-content p { margin: 0; color: rgba(255,255,255,0.8); font-size: 0.85rem; }

    .month-picker-box {
        background: white; padding: 0.4rem 0.85rem; border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1); display: flex; flex-direction: column; min-width: 150px;
    }
    .month-picker-box label { color: #64748b; font-size: 0.6rem; font-weight: 800; text-transform: uppercase; margin-bottom: 2px; }
    .month-input-clean { border: none !important; padding: 0 !important; font-weight: 700; color: #1e293b; font-size: 0.9rem; cursor: pointer; outline: none !important; width: 100%; }

    .stat-card-custom {
        background: white; border-radius: 24px; border: 1px solid rgba(226,232,240,0.8);
        box-shadow: 0 10px 30px rgba(0,0,0,0.03); padding: 1.5rem; height: 100%;
    }

    .toolbar-card {
        background: white; border-radius: 20px; border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); padding: 1.25rem; margin-bottom: 1.5rem;
    }

    .table-calendar { border-collapse: separate; border-spacing: 4px; }
    .table-calendar td { border: 1px solid #f0f0f0 !important; border-radius: 8px; overflow: hidden; }
    .border-bottom-dotted { border-bottom: 1px dotted #dee2e6; }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .header-section {
            flex-direction: column;
            align-items: flex-start;
            padding: 1.25rem;
            gap: 1rem;
        }
        
        .header-section > .d-flex {
            width: 100%;
            flex-direction: column;
            align-items: stretch !important;
            gap: 0.75rem !important;
        }

        .month-picker-box {
            width: 100%;
            min-width: unset !important;
        }

        .header-content h3 {
            font-size: 1.25rem;
        }
    }
</style>

<div class="container-fluid px-md-4 py-4">
    {{-- Header Utama (Statistik Pribadi) --}}
    <div class="header-section">
        <div class="header-content">
            <h3>Statistik Absensi</h3>
            <p>Pantau analisis poin dan riwayat absensi bulanan Anda.</p>
        </div>
        <div class="d-flex align-items-center gap-3">
             {{-- Pilih Bulan --}}
             <div class="month-picker-box">
                <label>Pilih Bulan</label>
                <input type="month" id="bulanPicker" value="{{ $bulan }}" class="month-input-clean">
             </div>

             {{-- Profil User (Gaya sama dengan Pilih Bulan) --}}
             <div class="month-picker-box d-none d-lg-flex">
                <label>Pegawai</label>
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-person-circle text-primary"></i>
                    <span style="font-weight: 700; color: #1e293b; font-size: 0.9rem;">{{ $user->nama }}</span>
                </div>
             </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Sisi Kiri: Ringkasan Poin --}}
        <div class="col-lg-5 col-md-6">
            <div class="stat-card-custom">
                <h6 class="fw-bold mb-4 text-secondary text-uppercase small" style="letter-spacing: 1px;">Analisis Poin</h6>
                <div class="row align-items-center mb-4 pb-4 border-bottom">
                    <div class="col-sm-6 text-center">
                        <div style="position: relative; width: 160px; height: 160px; margin: 0 auto;">
                            <canvas id="pieChart"></canvas>
                            <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;">
                                <div class="fw-bold text-dark fs-4 lh-1">{{ $totalPoin }}</div>
                                <div class="text-muted small" style="font-size: 0.6rem;">POIN</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 mt-3 mt-sm-0">
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

                <h6 class="fw-bold mb-3 text-secondary text-uppercase small" style="letter-spacing: 1px;">Ranking Poin</h6>
                <div class="row g-3">
                    <div class="col-6">
                        <h6 class="fw-bold text-success" style="font-size: 0.7rem;">5 TERATAS</h6>
                        <div class="list-group list-group-flush">
                            @forelse($top5Global as $u)
                                <div class="list-group-item px-0 py-1 border-0 bg-transparent d-flex justify-content-between align-items-center small">
                                    <span class="text-truncate me-1 small"><span class="text-muted">{{ $loop->iteration }}.</span> {{ $u->nama }}</span>
                                    <span class="fw-bold text-success small">{{ $u->poin_total }}</span>
                                </div>
                            @empty <div class="small text-muted">Kosong</div> @endforelse
                        </div>
                    </div>
                    <div class="col-6 border-start">
                        <h6 class="fw-bold text-danger" style="font-size: 0.7rem;">5 TERBAWAH</h6>
                        <div class="list-group list-group-flush">
                            @forelse($bottom5Global as $u)
                                <div class="list-group-item px-0 py-1 border-0 bg-transparent d-flex justify-content-between align-items-center small">
                                    <span class="text-truncate me-1 small"><span class="text-muted">{{ $loop->iteration }}.</span> {{ $u->nama }}</span>
                                    <span class="fw-bold text-danger small">{{ $u->poin_total }}</span>
                                </div>
                            @empty <div class="small text-muted">Kosong</div> @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sisi Kanan: Kalender --}}
        <div class="col-lg-7 col-md-6">
            <div class="stat-card-custom">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0 text-secondary text-uppercase small" style="letter-spacing: 1px;">Log Absensi Bulanan</h6>
                    <a href="#" id="btnExportCsv" class="btn btn-outline-success btn-sm px-3 rounded-pill fw-bold">
                        <i class="bi bi-download me-1"></i> Export CSV
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-calendar mb-0">
                        <thead class="bg-light text-center small">
                            <tr><th>SEN</th><th>SEL</th><th>RAB</th><th>KAM</th><th>JUM</th></tr>
                        </thead>
                        <tbody>
                            @php
                                $startDate = Carbon::parse($bulan.'-01');
                                $firstDayPadding = $startDate->dayOfWeekIso - 1;
                            @endphp
                            <tr>
                            @for ($i = 0; $i < $firstDayPadding; $i++) <td class="bg-light-subtle"></td> @endfor
                            @for ($day = 1; $day <= $hariDalamBulan; $day++)
                                @php $currentDate = Carbon::parse($bulan.'-'.$day); @endphp
                                @if ($currentDate->dayOfWeekIso == 1 && $day > 1) </tr><tr> @endif
                                @if (!$currentDate->isWeekend())
                                    @php
                                        $tanggalString = $currentDate->toDateString();
                                        $absen = $absensi->firstWhere('tanggal', $tanggalString);
                                        $status = '';
                                        $statusConfig = \App\Models\Setting::get('status', ['hari_libur' => '']);
                                        $hariLiburArr = explode("\n", str_replace("\r", "", $statusConfig['hari_libur'] ?? ''));
                                        
                                        $jamConfig = array_merge(['batas_hadir' => '08:00:00'], \App\Models\Setting::get('jam', []));

                                        if (in_array($tanggalString, $hariLiburArr)) { 
                                            $status = 'LIBUR'; 
                                        } elseif ($day <= $maxHari) {
                                            if ($absen) { 
                                                $status = $absen->status; 
                                            } else {
                                                $isTodayBeforeAlpha = $currentDate->isToday() && (Carbon::now($tz)->format('H:i:s') <= $jamConfig['batas_hadir']);
                                                if (!$isTodayBeforeAlpha) { $status = 'Tanpa Keterangan'; }
                                            }
                                        }
                                        $bgColor = $status === 'LIBUR' ? '#fff1f2' : '#ffffff';
                                        $borderColor = $status === 'LIBUR' ? '#f43f5e' : ($status ? ($statusColors[$status] ?? 'transparent') : 'transparent');
                                        $textColor = $status === 'LIBUR' ? 'text-danger' : 'text-dark';
                                    @endphp
                                    <td class="p-0 position-relative" style="height: 75px;">
                                        <div class="h-100 p-2 d-flex flex-column" style="background-color: {{ $status && $status !== 'LIBUR' ? $statusColors[$status].'22' : $bgColor }}; border-left: 3px solid {{ $borderColor }};">
                                            <div class="fw-bold {{ $currentDate->isToday() ? 'text-primary' : $textColor }}" style="font-size: 0.85rem;">{{ $day }}</div>
                                            @if($status) <div class="mt-auto small" style="font-size: 0.55rem; text-wrap: balance;">{{ $status }}</div> @endif
                                        </div>
                                    </td>
                                @endif
                            @endfor
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================= --}}
    {{-- PANTAU ANGGOTA (HANYA UNTUK KEPALA BIDANG / KEPALA DINAS) --}}
    {{-- ================================================================= --}}
    @if($isAtasan)
    <div id="atasan-section" class="mt-5 pt-4 border-top">
        {{-- Header Ala Admin (Filter Terintegrasi) --}}
        <form method="get" action="#atasan-section">
            <div class="header-section" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                <div class="header-content">
                    <h3>{{ auth()->user()->level === 'kadin' ? 'Pantau Seluruh Pegawai' : 'Pantau Anggota Bidang' }}</h3>
                    <p>Analisis kedisiplinan pegawai secara realtime berdasarkan hirarki jabatan Anda.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    {{-- Pilih Anggota --}}
                    <div class="month-picker-box" style="min-width: 180px;">
                        <label>Pilih Pegawai</label>
                        <select name="sub_id" id="subIdPicker" class="month-input-clean form-select shadow-none" style="background: none !important;" onchange="this.form.submit()">
                            <option value="">-- Pilih Nama --</option>
                            @foreach($subordinates as $sub)
                                <option value="{{ $sub->id }}" @selected(request('sub_id') == $sub->id)>{{ $sub->nama }} ({{ strtoupper($sub->bidang ?? 'Umum') }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Pilih Bulan --}}
                    <div class="month-picker-box">
                        <label>Pilih Bulan</label>
                        <input type="month" name="bulan" value="{{ $bulan }}" class="month-input-clean" onchange="this.form.submit()">
                    </div>
                </div>
            </div>
        </form>

        @if($targetSub && $subStats)
            <div class="row g-4">
                {{-- Statistik Poin Anggota --}}
                <div class="col-lg-5 col-md-6">
                    <div class="stat-card-custom">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="fw-bold mb-0 text-secondary text-uppercase small" style="letter-spacing: 1px;">Analisis Poin: {{ $targetSub->nama }}</h6>
                            <span class="badge bg-primary-subtle text-primary border-primary">Anggota</span>
                        </div>
                        
                        {{-- Chart di Atas --}}
                        <div class="text-center mb-4 pb-2">
                            <div style="position: relative; width: 140px; height: 140px; margin: 0 auto;">
                                <canvas id="subPieChart"></canvas>
                                <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;">
                                    <div class="fw-bold text-dark fs-4 lh-1">{{ $subStats['poin'] }}</div>
                                    <div class="text-muted small" style="font-size: 0.5rem;">POIN</div>
                                </div>
                            </div>
                        </div>

                        {{-- Keterangan di Bawah --}}
                        <div class="mt-2">
                            @foreach($statusColors as $s=>$color)
                                <div class="d-flex align-items-center justify-content-between mb-1 pb-1 border-bottom-dotted" style="font-size: 0.7rem;">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle me-2" style="width:6px; height:6px; background-color: {{ $color }};"></div>
                                        <span class="text-muted">{{ $s }}</span>
                                    </div>
                                    <span class="fw-bold">{{ $subStats['rekap'][$s] ?? 0 }}x</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Kalender Anggota --}}
                <div class="col-lg-7 col-md-6">
                    <div class="stat-card-custom">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="fw-bold mb-0 text-secondary text-uppercase small" style="letter-spacing: 1px;">Log Absensi: {{ $targetSub->nama }}</h6>
                            <a href="#" id="btnExportCsvSub" class="btn btn-outline-success btn-sm px-3 rounded-pill fw-bold">
                                <i class="bi bi-download me-1"></i> Export CSV
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-calendar mb-0 small">
                                <thead><tr class="text-center"><th>SEN</th><th>SEL</th><th>RAB</th><th>KAM</th><th>JUM</th></tr></thead>
                                <tbody>
                                    @php
                                        $sDate = Carbon::parse($bulan.'-01');
                                        $fP = $sDate->dayOfWeekIso - 1;
                                    @endphp
                                    <tr>
                                    @for($i=0; $i<$fP; $i++) <td class="bg-light-subtle"></td> @endfor
                                    @for($day=1; $day<=$hariDalamBulan; $day++)
                                        @php $curr = Carbon::parse($bulan.'-'.$day); @endphp
                                        @if($curr->dayOfWeekIso == 1 && $day > 1) </tr><tr> @endif
                                        @if(!$curr->isWeekend())
                                            @php
                                                $abs = $subStats['absensi']->firstWhere('tanggal', $curr->toDateString());
                                                $st = '';
                                                $isPending = false;
                                                $isRejected = false;
                                                $origStatus = '';

                                                $sConfig = \App\Models\Setting::get('status', ['hari_libur' => '']);
                                                $hLArr = explode("\n", str_replace("\r", "", $sConfig['hari_libur'] ?? ''));
                                                $jConfig = array_merge(['batas_hadir' => '08:00:00'], \App\Models\Setting::get('jam', []));

                                                if (in_array($curr->toDateString(), $hLArr)) {
                                                    $st = 'LIBUR';
                                                } elseif ($day <= $maxHari) {
                                                    if ($abs) {
                                                        $isPending = !$abs->is_approved && !$abs->is_rejected;
                                                        $isRejected = $abs->is_rejected;
                                                        $origStatus = $abs->status;
                                                        $st = $isRejected ? 'Tanpa Keterangan' : $abs->status;
                                                    } else {
                                                        $isTBA = $curr->isToday() && (now($tz)->format('H:i:s') <= $jConfig['batas_hadir']);
                                                        if (!$isTBA) {
                                                            $st = 'Tanpa Keterangan';
                                                        }
                                                    }
                                                }
                                                
                                                $bC = '#ffffff';
                                                if($st === 'LIBUR') {
                                                    $bC = '#f43f5e';
                                                } elseif($st) {
                                                    $bC = $statusColors[$st] ?? '#94a3b8';
                                                }
                                            @endphp
                                            <td class="p-0" style="height: 60px;">
                                                <div class="h-100 p-2 d-flex flex-column" style="background-color: {{ $st ? ($st === 'LIBUR' ? '#fff1f2' : ($isPending ? '#f1f5f9' : $bC.'22')) : '#fff' }}; border-left: 2px solid {{ $isPending ? '#94a3b8' : $bC }};">
                                                    <div class="fw-bold {{ $st === 'LIBUR' ? 'text-danger' : '' }}" style="font-size: 0.75rem;">{{ $day }}</div>
                                                    <div class="mt-auto" style="font-size: 0.5rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; {{ $st === 'LIBUR' ? 'color: #f43f5e; font-weight: bold;' : '' }}">
                                                        {{ $st }} @if($isPending) <i class="bi bi-clock-history"></i> @endif
                                                    </div>
                                                    @if($isRejected)
                                                        <div class="text-danger" style="font-size: 0.45rem;">({{ $origStatus }})</div>
                                                    @endif
                                                </div>
                                            </td>
                                        @endif
                                    @endfor
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="stat-card-custom text-center py-5">
                <i class="bi bi-person-check fs-1 text-muted opacity-50"></i>
                <h5 class="fw-bold text-muted mt-3">Silakan pilih salah satu anggota bidang untuk memantau data</h5>
            </div>
        @endif
    </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('pieChart').getContext('2d');
new Chart(ctx,{
    type:'doughnut',
    data:{
        labels: {!! json_encode(array_keys($statusColors)) !!},
        datasets:[{
            data: {!! json_encode(array_values($rekapData)) !!},
            backgroundColor: {!! json_encode(array_values($statusColors)) !!},
            borderWidth:2, borderColor: '#ffffff'
        }]
    },
    options:{responsive:true,cutout:'75%',plugins:{legend:{display:false}}}
});

document.getElementById('bulanPicker').addEventListener('change',function(){
    window.location.href="?bulan="+this.value;
});

document.getElementById('btnExportCsv').addEventListener('click', function(e){
    e.preventDefault();
    const bulan = document.getElementById('bulanPicker').value;
    window.location.href = "/statistik/export/csv?bulan=" + bulan;
});

const btnExportCsvSub = document.getElementById('btnExportCsvSub');
if(btnExportCsvSub) {
    btnExportCsvSub.addEventListener('click', function(e){
        e.preventDefault();
        const bulan = document.getElementById('bulanPicker').value;
        const subId = document.getElementById('subIdPicker').value;
        window.location.href = "/statistik/export/csv?bulan=" + bulan + "&user_id=" + subId;
    });
}

@if($isAtasan && $targetSub && $subStats)
const subCtx = document.getElementById('subPieChart').getContext('2d');
new Chart(subCtx,{
    type:'doughnut',
    data:{
        labels: {!! json_encode(array_keys($statusColors)) !!},
        datasets:[{
            data: {!! json_encode(array_values($subStats['rekap'])) !!},
            backgroundColor: {!! json_encode(array_values($statusColors)) !!},
            borderWidth:2, borderColor:'#ffffff'
        }]
    },
    options:{responsive:true,cutout:'75%',plugins:{legend:{display:false}}}
});
@endif
</script>
@endpush
@endsection
