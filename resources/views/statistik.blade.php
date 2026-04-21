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
        // Abaikan data absensi yang ada jika tanggalnya adalah hari Sabtu atau Minggu
        if (!$tanggalAbsen->isWeekend()) {
            $status = $absen->status;
            $rekapData[$status] += 1;
            
            // Ambil kunci poin yang sesuai
            $key = $poinKeyMap[$status] ?? null;
            if($key && isset($poinConfig[$key])){
                // Kasus khusus untuk terlambat tanpa alasan
                if($status === 'Terlambat' && empty(trim($absen->alasan ?? '')) ){
                    $totalPoin += (int) ($poinConfig['alpha'] ?? 0);
                } else {
                    $totalPoin += (int) $poinConfig[$key];
                }
            }
        }
    } else {
        $tanggalLoop = Carbon::parse($tgl);
        $isWeekend = $tanggalLoop->isWeekend();
        $isTodayBeforeCutoff = $tanggalLoop->isToday() && (Carbon::now('Asia/Makassar')->format('H:i:s') <= config('absensi.cutoff', '16:00:00'));

        if (!$isWeekend && !$isTodayBeforeCutoff) {
            $rekapData['Tanpa Keterangan'] += 1;
            // Tambahkan poin untuk alpha (Tanpa Keterangan)
            $totalPoin += (int) ($poinConfig['alpha'] ?? 0);
        }
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
                <div class="mb-4">
                    <h6 class="fw-bold mb-3 d-flex align-items-center">
                        <i class="bi bi-trophy-fill text-warning me-2"></i> 5 Poin Tertinggi
                    </h6>
                    <div class="list-group list-group-flush border rounded-3 overflow-hidden">
                        @forelse($top5Global as $index => $u)
                            <div class="list-group-item d-flex align-items-center py-2 px-3 border-light">
                                <div class="me-3 d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; border-radius: 50%; background: {{ $index == 0 ? 'linear-gradient(45deg, #FFD700, #FFCC00)' : ($index == 1 ? 'linear-gradient(45deg, #C0C0C0, #E8E8E8)' : ($index == 2 ? 'linear-gradient(45deg, #CD7F32, #E9967A)' : '#f1f5f9')) }}; color: {{ $index <= 2 ? '#fff' : '#64748b' }}; font-size: {{ $index <= 2 ? '1.1rem' : '0.75rem' }};">
                                    @if($index == 0)
                                        <i class="bi bi-award-fill"></i>
                                    @elseif($index == 1)
                                        <i class="bi bi-award-fill"></i>
                                    @elseif($index == 2)
                                        <i class="bi bi-award-fill"></i>
                                    @else
                                        {{ $index + 1 }}
                                    @endif
                                </div>
                                <div class="flex-grow-1 fw-medium text-dark small">
                                    {{ $u->nama }}
                                </div>
                                <div class="badge bg-success-subtle text-success rounded-pill px-2 py-1" style="font-size: 0.7rem;">{{ $u->poin_total }} pts</div>
                            </div>
                        @empty
                            <div class="list-group-item text-center py-3 text-muted small">Belum ada data</div>
                        @endforelse
                    </div>
                </div>

                <div>
                    <h6 class="fw-bold mb-3 d-flex align-items-center">
                        <i class="bi bi-graph-down-arrow text-danger me-2"></i> 5 Poin Terendah
                    </h6>
                    <div class="list-group list-group-flush border rounded-3 overflow-hidden">
                        @forelse($bottom5Global as $index => $u)
                            <div class="list-group-item d-flex align-items-center py-2 px-3 border-light">
                                <div class="me-3 d-flex align-items-center justify-content-center fw-bold text-muted" style="width: 28px; height: 28px; border-radius: 50%; background: #fff1f2; font-size: 0.75rem;">
                                    #{{ $loop->iteration }}
                                </div>
                                <div class="flex-grow-1 fw-medium text-dark small">{{ $u->nama }}</div>
                                <div class="badge bg-danger-subtle text-danger rounded-pill px-2 py-1" style="font-size: 0.7rem;">{{ $u->poin_total }} pts</div>
                            </div>
                        @empty
                            <div class="list-group-item text-center py-3 text-muted small">Belum ada data</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Rekap Harian --}}
    <div id="rekapHarianContainer" class="card p-3 mt-4" style="display:none;">
        <h5>Rekap Kehadiran Bulan {{ Carbon::parse($bulan.'-01')->isoFormat('MMMM YYYY') }}</h5>
        <table class="table table-bordered table-sm text-center">
            <thead class="table-light">
                <tr>
                    @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum'] as $dayName)
                        <th>{{ $dayName }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php
                    $startDate = Carbon::parse($bulan.'-01');
                    $firstDayPadding = $startDate->dayOfWeekIso - 1;
                @endphp
                <tr>
                {{-- Render sel kosong untuk padding di awal bulan --}}
                @for ($i = 0; $i < $firstDayPadding; $i++)
                    <td></td>
                @endfor

                @for ($day = 1; $day <= $hariDalamBulan; $day++)
                    @php
                        $currentDate = Carbon::parse($bulan.'-'.$day);
                        // Jika hari Senin (dan bukan hari pertama), mulai baris baru
                        if ($currentDate->dayOfWeekIso == 1 && $day > 1) {
                            echo '</tr><tr>';
                        }
                    @endphp

                    {{-- Hanya render sel jika hari kerja --}}
                    @if (!$currentDate->isWeekend())
                        @php
                            $absen = $absensiBulan->firstWhere('tanggal', $currentDate->toDateString());
                            $status = ''; // Default status kosong

                            if ($day <= $maxHari) {
                                if ($absen) {
                                    $status = $absen->status;
                                } else {
                                    // Logika untuk menentukan 'Tanpa Keterangan'
                                    $isTodayBeforeCutoff = $currentDate->isToday() && (Carbon::now('Asia/Makassar')->format('H:i:s') <= config('absensi.cutoff', '16:00:00'));
                                    if (!$isTodayBeforeCutoff) {
                                        $status = 'Tanpa Keterangan';
                                    }
                                }
                            }
                        @endphp
                        <td @if($status) style="background-color: {{ $statusColors[$status] ?? '#fff' }}; color:#000;" @endif>
                            <div>{{ $day }}</div>
                            @if($status)<small class="text-muted">{{ $status }}</small>@endif
                        </td>
                    @endif
                @endfor

                {{-- Render sel kosong untuk padding di akhir bulan --}}
                @php
                    $lastDayOfMonth = Carbon::parse($bulan.'-'.$hariDalamBulan);
                    // Hanya pad jika bulan tidak berakhir di hari Jumat
                    if ($lastDayOfMonth->dayOfWeekIso < 5) {
                        $lastDayPadding = 5 - $lastDayOfMonth->dayOfWeekIso;
                        for ($i = 0; $i < $lastDayPadding; $i++) {
                            echo '<td></td>';
                        }
                    }
                @endphp
                </tr>
            </tbody>
        </table>
    </div>

</div>

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

document.getElementById('btnExportCsv').addEventListener('click', function(e){
    e.preventDefault();
    const bulan = document.getElementById('bulanPicker').value;
    window.location.href = "/statistik/export/csv?bulan=" + bulan;
});
</script>
@endpush
@endsection