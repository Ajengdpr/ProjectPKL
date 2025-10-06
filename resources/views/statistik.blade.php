@extends('layouts.app')
@section('title','Statistik Kehadiran')

@section('content')
@php
use Carbon\Carbon;
use App\Models\Absensi;

// Ambil pemetaan status terpusat yang dibutuhkan oleh kalender rekap
$statuses = Absensi::getStatuses();
$bulan = request('bulan', date('Y-m'));
$absensiBulan = $absensi->filter(fn($a) => Carbon::parse($a->tanggal)->format('Y-m') === $bulan);

// statusColors, totalPoin, dan rekapData sudah di-pass dari controller
@endphp

<div class="container" style="max-width:900px">
    <h3 class="fw-bold mb-3">Statistik Kehadiran {{ $user->nama }}</h3>

    {{-- Pilih Bulan --}}
    <div class="mb-3 d-flex gap-2 align-items-center flex-wrap">
        <input type="month" id="bulanPicker" value="{{ request('bulan', date('Y-m')) }}" class="form-control form-control-sm" style="max-width:150px">
        <button id="btnLihatRekap" class="btn btn-primary btn-sm">Lihat Rekap</button>
        <a href="#" id="btnExportCsv" class="btn btn-success btn-sm">Export CSV</a>
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
        <h5>Rekap Kehadiran Bulan {{ \Carbon\Carbon::parse(request('bulan', date('Y-m')).'-01')->isoFormat('MMMM YYYY') }}</h5>
        <table class="table table-bordered table-sm text-center">
            <thead class="table-light">
                <tr>
                    <th>Sen</th>
                    <th>Sel</th>
                    <th>Rab</th>
                    <th>Kam</th>
                    <th>Jum</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $cal_firstDay = Carbon::parse($bulan.'-01');
                    $cal_dayCounter = $cal_firstDay->copy();
                    $cal_calendarDays = [];

                    // Add empty cells for Monday-Friday before the month starts
                    $cal_startOffset = $cal_firstDay->dayOfWeekIso - 1; // 0 for Mon, 6 for Sun
                    for ($i = 0; $i < $cal_startOffset; $i++) {
                        $cal_calendarDays[] = null;
                    }

                    // Loop through the actual days of the month and add only weekdays
                    while ($cal_dayCounter->month == $cal_firstDay->month) {
                        if (!$cal_dayCounter->isWeekend()) {
                            $cal_calendarDays[] = $cal_dayCounter->copy();
                        }
                        $cal_dayCounter->addDay();
                    }
                    
                    // Chunk the flat array of weekdays into weeks (rows) of 5 days
                    $cal_weeks = array_chunk($cal_calendarDays, 5);
                @endphp

                @foreach($cal_weeks as $week)
                    <tr>
                        @foreach($week as $day)
                            @if($day === null)
                                <td></td>
                            @else
                                @php
                                    $dayOfMonth = $day->day;
                                    $tgl = $day->format('Y-m-d');
                                    $absen = $absensiBulan->firstWhere('tanggal', $tgl);
                                    $statusLabel = ''; // Default to empty

                                    if ($day->isPast() || $day->isToday()) {
                                        if ($absen) {
                                            $statusLabel = $statuses[$absen->status] ?? $absen->status;
                                        } else {
                                            $statusLabel = $statuses['alpha']; // Tanpa Keterangan
                                        }
                                    }
                                @endphp
                                <td style="background-color: {{ $statusColors[$statusLabel] ?? '#fff' }}; color: #000;">
                                    <div>{{ $dayOfMonth }}</div>
                                    @if($statusLabel)
                                        <small class="text-muted">{{ $statusLabel }}</small>
                                    @endif
                                </td>
                            @endif
                        @endforeach
                        {{-- Pad the last row with empty cells if it's not a full 5-day week --}}
                        @if(count($week) < 5)
                            @for($i = count($week); $i < 5; $i++)
                                <td></td>
                            @endfor
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('pieChart').getContext('2d');

// Data dari PHP
const rekapData = @json($rekapData);
const statusLabels = @json($statusLabels);
const statusColors = @json($statusColors);

// Memastikan urutan data dan warna cocok dengan urutan label
const orderedData = statusLabels.map(label => rekapData[label] || 0);
const orderedColors = statusLabels.map(label => statusColors[label] || '#e0e0e0');

const pieData = {
    labels: statusLabels,
    datasets: [{
        data: orderedData,
        backgroundColor: orderedColors,
        borderWidth: 1
    }]
};

if (!{{ $adaData ? 'true':'false' }}) {
    pieData.datasets[0].data = [1];
    pieData.labels = ['Tidak ada data'];
    pieData.datasets[0].backgroundColor = ['#e0e0e0'];
}

new Chart(ctx, {
    type: 'doughnut',
    data: pieData,
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

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

document.getElementById('btnExportCsv').addEventListener('click', function(e){
    e.preventDefault();
    const bulan = document.getElementById('bulanPicker').value;
    window.location.href = "/statistik/export/csv?bulan=" + bulan;
});
</script>
@endpush
@endsection