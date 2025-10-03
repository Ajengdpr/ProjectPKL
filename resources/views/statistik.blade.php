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
    'Hadir(+1)' => '#36A2EB',
    'Izin(+0)' => '#FFCE56',
    'Cuti(+0)' => '#9966FF',
    'Sakit(+0)' => '#FF6384',
    'Terlambat(-3)' => '#4BC0C0',
    'Tugas Luar(+0)' => '#FF9F40',
    'Tanpa Keterangan(-5)' => '#e0e0e0'
];

$rekapData = [
    'Hadir'=>0, 'Izin'=>0, 'Cuti'=>0, 'Sakit'=>0, 'Terlambat'=>0, 'Tugas Luar'=>0, 'Tanpa Keterangan'=>0
];

$totalPoin = 0;

// Hitung rekap dan poin hanya sampai maxHari
for($i=1; $i<=$maxHari; $i++){
    $absen = $absensiBulan->firstWhere('tanggal',$tgl);
    if($absen){
        $status = $absen->status;
        $rekapData[$status] += 1;
        if($status === 'Hadir') $totalPoin += 1;
        elseif($status === 'Terlambat') $totalPoin += ($absen->alasan?-3:-5);
    } else {
        $rekapData['Tanpa Keterangan'] += 1;
    }
}

$adaData = array_sum($rekapData) > 0;
@endphp

<div class="container" style="max-width:900px">
    <h3 class="fw-bold mb-3">Statistik Kehadiran {{ $user->nama }}</h3>

    {{-- Pilih Bulan --}}
    <div class="mb-3 d-flex gap-2 align-items-center flex-wrap">
        <input type="month" id="bulanPicker" value="{{ $bulan }}" class="form-control form-control-sm" style="max-width:150px">
        <button id="btnLihatRekap" class="btn btn-primary btn-sm">Lihat Rekap</button>
        <button id="btnTutupRekap" class="btn btn-secondary btn-sm" style="display:none;">Tutup</button>
    </div>

    {{-- Card utama --}}
    <div class="card p-3 mb-4">
        <div class="row justify-content-center align-items-start">
            {{-- Donut + Legend --}}
            <div class="col-md-4 d-flex flex-column align-items-center mb-3">
                <div style="position: relative; width:100%; max-width:250px; height:250px;">
                    <canvas id="pieChart" height="250"></canvas>
                    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;">
                        <div class="fw-bold">Total Poin</div>
                        <div class="display-6 fw-bold">{{ $totalPoin }}</div>
                    </div>
                </div>
                <div class="mt-3 w-100">
                    @foreach($statusColors as $s=>$color)
                        <div class="d-flex align-items-center mb-1">
                            <span class="me-2" style="display:inline-block;width:20px;height:20px;background-color: {{ $color }};"></span>
                            <span>{{ $s }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Ranking --}}
            <div class="col-md-8">
                <h6 class="fw-bold">5 Poin Tertinggi</h6>
                <table class="table table-sm table-bordered mb-3">
                    <thead><tr><th>#</th><th>Nama</th><th>Poin</th></tr></thead>
                    <tbody>
                        @forelse($top5Global as $u)
                        <tr class="@if($loop->iteration <=3) table-success @endif">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $u->nama }}</td>
                            <td>{{ $u->poin_total }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3"><em>Tidak ada data</em></td></tr>
                        @endforelse
                    </tbody>
                </table>

                <h6 class="fw-bold">5 Poin Terendah</h6>
                <table class="table table-sm table-bordered">
                    <thead><tr><th>#</th><th>Nama</th><th>Poin</th></tr></thead>
                    <tbody>
                        @forelse($bottom5Global as $u)
                        <tr class="table-danger">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $u->nama }}</td>
                            <td>{{ $u->poin_total }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3"><em>Tidak ada data</em></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Rekap Harian --}}
    <div id="rekapHarianContainer" class="card p-3 mt-4" style="display:none;">
        <h5>Rekap Kehadiran Bulan {{ Carbon::parse($bulan.'-01')->isoFormat('MMMM YYYY') }}</h5>
        <table class="table table-bordered table-sm text-center">
            <thead class="table-light">
                <tr>
                    @for($d=0;$d<7;$d++)
                        <th>{{ Carbon::create()->startOfWeek()->addDays($d)->isoFormat('ddd') }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @php
                $startDay = Carbon::parse($bulan.'-01')->dayOfWeek;
                $currentDay = 1;
                @endphp
                @for($week=0; $currentDay<=$hariDalamBulan; $week++)
                    <tr>
                        @for($d=0;$d<7;$d++)
                            @if($week===0 && $d<$startDay)
                                <td></td>
                            @elseif($currentDay <= $hariDalamBulan)
                                @php
                                    $tgl = Carbon::parse($bulan.'-'.str_pad($currentDay,2,'0',STR_PAD_LEFT))->format('Y-m-d');
                                    $absen = $absensiBulan->firstWhere('tanggal',$tgl);
                                    if($currentDay <= $maxHari){
                                        $status = $absen->status ?? 'Tanpa Keterangan';
                                    } else {
                                        $status = '';
                                    }
                                @endphp
                                <td @if($status) style="background-color: {{ $statusColors[$status] ?? '#fff' }}; color:#000;" @endif>
                                    <div>{{ $currentDay }}</div>
                                    @if($status)<small class="text-muted">{{ $status }}</small>@endif
                                </td>
                                @php $currentDay++; @endphp
                            @else
                                <td></td>
                            @endif
                        @endfor
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>

</div>

{{-- Bottom nav --}}
<nav class="bottom-nav mt-4">
  <div class="container">
    <ul class="nav justify-content-around py-2">
      <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}"><i class="bi bi-house-door me-1"></i> Home</a></li>
      <li class="nav-item"><a class="nav-link active" href="{{ route('statistik') }}"><i class="bi bi-graph-up me-1"></i> Statistik</a></li>
      <li class="nav-item"><a class="nav-link" href="{{ route('account') }}"><i class="bi bi-person me-1"></i> Account</a></li>
    </ul>
  </div>
</nav>

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

const btnLihat=document.getElementById('btnLihatRekap');
const btnTutup=document.getElementById('btnTutupRekap');
const container=document.getElementById('rekapHarianContainer');
btnLihat.addEventListener('click',function(){
    container.style.display='block';
    btnLihat.style.display='none';
    btnTutup.style.display='inline-block';
    container.scrollIntoView({behavior:'smooth'});
});
btnTutup.addEventListener('click',function(){
    container.style.display='none';
    btnLihat.style.display='inline-block';
    btnTutup.style.display='none';
});
</script>
@endpush
@endsection
