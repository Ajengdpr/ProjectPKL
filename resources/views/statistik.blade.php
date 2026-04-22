@extends('layouts.app')
@section('title','Statistik Kehadiran')

@section('content')
@php
use Carbon\Carbon;

$bulan = request('bulan', date('Y-m'));
$absensiBulan = $absensi->filter(fn($a) => Carbon::parse($a->tanggal)->format('Y-m') === $bulan);

$hariDalamBulan = Carbon::parse($bulan.'-01')->daysInMonth;
$now = Carbon::now();

// Tentukan batas hari yang dihitung
if(Carbon::parse($bulan.'-01')->format('Y-m') < $now->format('Y-m')){
    $maxHari = $hariDalamBulan; // bulan lalu
} elseif(Carbon::parse($bulan.'-01')->format('Y-m') == $now->format('Y-m')){
    $maxHari = $now->day; // bulan ini
} else {
    $maxHari = 0; // bulan depan
}

$statusColors = [
    'Hadir' => '#36A2EB',
    'Izin' => '#FFCE56',
    'Cuti' => '#9966FF',
    'Sakit' => '#FF6384',
    'Terlambat' => '#4BC0C0',
    'Tugas Luar' => '#FF9F40',
    'Tanpa Keterangan' => '#e0e0e0'
];

$rekapData = [
    'Hadir'=>0, 'Izin'=>0, 'Cuti'=>0, 'Sakit'=>0, 'Terlambat'=>0, 'Tugas Luar'=>0, 'Tanpa Keterangan'=>0
];

$totalPoin = 0;

// Definisikan pemetaan dari status di database ke kunci di poinConfig
$poinKeyMap = [
    'Hadir'            => 'hadir',
    'Terlambat'        => 'terlambat',
    'Izin'             => 'izin',
    'Sakit'            => 'sakit',
    'Cuti'             => 'cuti',
    'Tugas Luar'       => 'tugas_luar',
    'Tanpa Keterangan' => 'alpha',
];

// Hitung rekap dan poin hanya sampai maxHari
for($i=1; $i<=$maxHari; $i++){
    $tgl = Carbon::parse($bulan.'-'.str_pad($i,2,'0',STR_PAD_LEFT))->format('Y-m-d');
    $absen = $absensiBulan->firstWhere('tanggal',$tgl);
    
    if($absen){
        $tanggalAbsen = Carbon::parse($absen->tanggal);
        if (!$tanggalAbsen->isWeekend()) {
            $status = $absen->status;
            $rekapData[$status] += 1;
            $key = $poinKeyMap[$status] ?? null;
            if($key && isset($poinConfig[$key])){
                if($status === 'Terlambat' && empty(trim($absen->alasan ?? '')) ){
                    $totalPoin += (int) ($poinConfig['alpha'] ?? 0);
                } else {
                    $totalPoin += (int) $poinConfig[$key];
                }
            }
        }
    } else {
        $tanggalLoop = Carbon::parse($tgl);
        if (!$tanggalLoop->isWeekend()) {
            $isTodayBeforeCutoff = $tanggalLoop->isToday() && (Carbon::now('Asia/Makassar')->format('H:i:s') <= config('absensi.cutoff', '16:00:00'));
            if (!$isTodayBeforeCutoff) {
                $rekapData['Tanpa Keterangan'] += 1;
                $totalPoin += (int) ($poinConfig['alpha'] ?? 0);
            }
        }
    }
}

$adaData = array_sum($rekapData) > 0;
@endphp

<div class="container-fluid py-4" style="max-width: 1200px;">
    <div class="d-flex justify-content-between align-items-end mb-4 border-bottom pb-2">
        <h3 class="fw-bold mb-0">Statistik Kehadiran <span class="text-primary">{{ $user->nama }}</span></h3>
        <div style="width: 160px;">
            <input type="month" id="bulanPicker" value="{{ $bulan }}" class="form-control form-control-sm shadow-sm">
        </div>
    </div>

    <div class="row g-4">
        {{-- Sisi Kiri: Ringkasan Poin & Ranking (Satu Kontainer) --}}
        <div class="col-lg-5 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    {{-- Bagian Atas: Donut & Keterangan --}}
                    <h6 class="fw-bold mb-4 text-secondary text-uppercase small" style="letter-spacing: 1px;">Statistik Poin</h6>
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

                    {{-- Bagian Bawah: Ranking Global --}}
                    <h6 class="fw-bold mb-3 text-secondary text-uppercase small" style="letter-spacing: 1px;">Ranking Global</h6>
                    <div class="row g-3">
                        <div class="col-6">
                            <h6 class="fw-bold text-success" style="font-size: 0.7rem;">5 TERATAS</h6>
                            <div class="list-group list-group-flush">
                                @forelse($top5Global as $u)
                                    <div class="list-group-item px-0 py-1 border-0 bg-transparent d-flex justify-content-between align-items-center small">
                                        <span class="text-truncate me-1"><span class="text-muted">{{ $loop->iteration }}.</span> {{ $u->nama }}</span>
                                        <span class="fw-bold text-success">{{ $u->poin_total }}</span>
                                    </div>
                                @empty
                                    <div class="small text-muted">Kosong</div>
                                @endforelse
                            </div>
                        </div>
                        <div class="col-6 border-start">
                            <h6 class="fw-bold text-danger" style="font-size: 0.7rem;">5 TERBAWAH</h6>
                            <div class="list-group list-group-flush">
                                @forelse($bottom5Global as $u)
                                    <div class="list-group-item px-0 py-1 border-0 bg-transparent d-flex justify-content-between align-items-center small">
                                        <span class="text-truncate me-1"><span class="text-muted">{{ $loop->iteration }}.</span> {{ $u->nama }}</span>
                                        <span class="fw-bold text-danger">{{ $u->poin_total }}</span>
                                    </div>
                                @empty
                                    <div class="small text-muted">Kosong</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sisi Kanan: Rekap Harian (Kalender) --}}
        <div class="col-lg-7 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold mb-0 text-secondary text-uppercase small" style="letter-spacing: 1px;">Log Kehadiran Bulanan</h6>
                        <a href="#" id="btnExportCsv" class="btn btn-outline-success btn-sm px-3 rounded-pill">
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
                                            $absen = $absensiBulan->firstWhere('tanggal', $currentDate->toDateString());
                                            $status = '';
                                            if ($day <= $maxHari) {
                                                if ($absen) { $status = $absen->status; } 
                                                else {
                                                    $isTodayBeforeCutoff = $currentDate->isToday() && (Carbon::now('Asia/Makassar')->format('H:i:s') <= config('absensi.cutoff', '16:00:00'));
                                                    if (!$isTodayBeforeCutoff) { $status = 'Tanpa Keterangan'; }
                                                }
                                            }
                                            
                                            $bgColor = '#ffffff';
                                            if($status) {
                                                $rawColor = $statusColors[$status] ?? '#f8f9fa';
                                                $bgColor = $rawColor . '22'; 
                                            }
                                        @endphp
                                        <td class="p-0 position-relative" style="height: 75px;">
                                            <div class="h-100 p-2 d-flex flex-column" style="background-color: {{ $bgColor }}; border-left: 3px solid {{ $statusColors[$status] ?? 'transparent' }};">
                                                <div class="fw-bold @if($currentDate->isToday()) text-primary @else text-dark @endif" style="font-size: 0.85rem;">
                                                    {{ $day }}
                                                </div>
                                                @if($status)
                                                    <div class="mt-auto">
                                                        <span class="badge p-0 text-dark fw-medium" style="font-size: 0.6rem; text-wrap: balance;">{{ $status }}</span>
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
    </div>
</div>

<style>
    .table-calendar { border-collapse: separate; border-spacing: 4px; }
    .table-calendar td { border: 1px solid #f0f0f0 !important; border-radius: 6px; overflow: hidden; }
    .border-bottom-dotted { border-bottom: 1px dotted #dee2e6; }
    .nav-pills .nav-link { color: #6c757d; border: 1px solid transparent; }
    .nav-pills .nav-link.active { background-color: #f8f9fa; color: #0d6efd; border-color: #dee2e6; }
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
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
        borderWidth:1
    }]
};
if(!{{ $adaData ? 'true':'false' }}){
    pieData.datasets[0].data=[1];
    pieData.labels=['Tidak ada data'];
    pieData.datasets[0].backgroundColor=['#e0e0e0'];
}
new Chart(ctx,{type:'doughnut',data:pieData,options:{responsive:true,plugins:{legend:{display:false}}}});

document.getElementById('bulanPicker').addEventListener('change',function(){
    window.location.href="?bulan="+this.value;
});

document.getElementById('btnExportCsv').addEventListener('click', function(e){
    e.preventDefault();
    const bulan = document.getElementById('bulanPicker').value;
    window.location.href = "/statistik/export/csv?bulan=" + bulan;
});
</script>
@endpush
@endsection