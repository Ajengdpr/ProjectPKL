@extends('layouts.admin')
@section('title','Dashboard Admin')

@section('content')
@php
  $badgeClass = function ($s) {
    return match($s) {
      'hadir'      => 'bg-success-subtle text-success',
      'terlambat'  => 'bg-warning-subtle text-warning',
      'izin'       => 'bg-primary-subtle text-primary',
      'sakit'      => 'bg-info-subtle text-info',
      'alpha'      => 'bg-danger-subtle text-danger',
      default      => 'bg-secondary-subtle text-secondary'
    };
  };
@endphp

<div class="container" style="max-width:1100px">

  {{-- HEADER + FILTER BAR --}}
  <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-md-between gap-2 mb-3">
    <h1 class="h3 fw-bold mb-0">Dashboard Admin — {{ $date }}</h1>

    <div class="app-card p-2 d-flex flex-wrap align-items-center gap-2">
      <form method="get" class="d-flex align-items-center gap-2">
        <input type="date" name="date" value="{{ $date }}" class="form-control" style="max-width: 180px;">
        <button class="btn btn-outline-secondary">Terapkan</button>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Hari Ini</a>
      </form>
      <a class="btn btn-outline-secondary"
         href="{{ route('admin.absensi.export.csv', ['from'=>$date,'to'=>$date]) }}">
        <i class="bi bi-download me-1"></i>Export CSV ({{ $date }})
      </a>
    </div>
  </div>

  {{-- STAT CARDS --}}
  <div class="row g-3">
    <div class="col-6 col-md-4 col-lg-2">
      <a href="{{ route('admin.users.index') }}" class="text-reset text-decoration-none">
        <div class="app-card p-3 h-100">
          <div class="small text-body-secondary">Total Pegawai</div>
          <div class="fs-3 fw-bold mt-1">{{ $totalPegawai }}</div>
        </div>
      </a>
    </div>

    <div class="col-6 col-md-4 col-lg-2">
      <a href="{{ route('admin.absensi.index', ['from'=>$date,'to'=>$date,'status'=>'hadir']) }}" class="text-reset text-decoration-none">
        <div class="app-card p-3 h-100 border-start border-4 border-success-subtle">
          <div class="small text-body-secondary">Hadir</div>
          <div class="fs-3 fw-bold text-success mt-1">{{ $hadir }}</div>
        </div>
      </a>
    </div>

    <div class="col-6 col-md-4 col-lg-2">
      <a href="{{ route('admin.absensi.index', ['from'=>$date,'to'=>$date,'status'=>'terlambat']) }}" class="text-reset text-decoration-none">
        <div class="app-card p-3 h-100 border-start border-4 border-warning-subtle">
          <div class="small text-body-secondary">Terlambat</div>
          <div class="fs-3 fw-bold text-warning mt-1">{{ $terlambat }}</div>
        </div>
      </a>
    </div>

    <div class="col-6 col-md-4 col-lg-2">
      <a href="{{ route('admin.absensi.index', ['from'=>$date,'to'=>$date,'status'=>'izin']) }}" class="text-reset text-decoration-none">
        <div class="app-card p-3 h-100">
          <div class="small text-body-secondary">Izin</div>
          <div class="fs-3 fw-bold mt-1">{{ $izin }}</div>
        </div>
      </a>
    </div>

    <div class="col-6 col-md-4 col-lg-2">
      <a href="{{ route('admin.absensi.index', ['from'=>$date,'to'=>$date,'status'=>'sakit']) }}" class="text-reset text-decoration-none">
        <div class="app-card p-3 h-100">
          <div class="small text-body-secondary">Sakit</div>
          <div class="fs-3 fw-bold mt-1">{{ $sakit }}</div>
        </div>
      </a>
    </div>

    <div class="col-6 col-md-4 col-lg-2">
      <a href="{{ route('admin.absensi.index', ['from'=>$date,'to'=>$date,'status'=>'alpha']) }}" class="text-reset text-decoration-none">
        <div class="app-card p-3 h-100 border-start border-4 border-danger-subtle">
          <div class="small text-body-secondary">Alpha</div>
          <div class="fs-3 fw-bold text-danger mt-1">{{ $alpha }}</div>
        </div>
      </a>
    </div>

    {{-- Attendance Rate --}}
    <div class="col-12 col-lg-4">
      <div class="app-card p-3 h-100">
        <div class="d-flex justify-content-between">
          <div class="small text-body-secondary">Hadir + Terlambat</div>
          <div class="small text-body-secondary">dari {{ $totalPegawai }}</div>
        </div>
        <div class="display-6 fw-bold mt-1">{{ $hadirTotal }}</div>

        <div class="mt-3">
          <div class="d-flex justify-content-between small">
            <span class="text-body-secondary">Attendance Rate</span>
            <strong>{{ $attendanceRate }}%</strong>
          </div>
          <div class="progress mt-2" style="height: 8px;">
            <div class="progress-bar bg-success" style="width: {{ $attendanceRate }}%"></div>
          </div>
        </div>
      </div>
    </div>

    {{-- Log Absensi Terbaru --}}
    <div class="col-12 col-lg-8">
      <div class="app-card p-3 h-100">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h6 class="fw-bold mb-0">Log Absensi Terbaru ({{ $date }})</h6>
          <a href="{{ route('admin.absensi.index', ['from'=>$date,'to'=>$date]) }}"
             class="small text-decoration-underline">Lihat semua</a>
        </div>

        <div class="table-responsive rounded border">
          <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="width:130px;">Tanggal</th>
                <th>Nama</th>
                <th style="width:120px;">Status</th>
                <th>Alasan</th>
              </tr>
            </thead>
            <tbody>
              @forelse($logTerbaru as $l)
                <tr>
                  <td>{{ $l->tanggal }}</td>
                  <td class="fw-medium">{{ $l->user->nama ?? '-' }}</td>
                  <td><span class="badge rounded-pill {{ $badgeClass($l->status) }}">{{ strtoupper($l->status) }}</span></td>
                  <td>{{ $l->alasan }}</td>
                </tr>
              @empty
                <tr><td colspan="4" class="text-center text-body-secondary py-3">Belum ada data tanggal ini.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  {{-- BOTTOM: Belum Absen + Rekap Bidang --}}
  <div class="row g-3 mt-1">
    <div class="col-12 col-lg-6">
      <div class="app-card p-3 h-100">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h6 class="fw-bold mb-0">Pegawai yang Belum Absen ({{ $belumAbsenCount }})</h6>
          <form method="get" class="d-none d-md-flex align-items-center gap-2">
            <input type="hidden" name="date" value="{{ $date }}">
            <select name="bidang" class="form-select form-select-sm" style="width: 180px;">
              <option value="">Semua Bidang</option>
              @foreach($allBidangs as $b)
                <option value="{{ $b }}" @selected(($filterBidang ?? '')==$b)>{{ $b }}</option>
              @endforeach
            </select>
            <input name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Cari nama..." style="width: 180px;">
            <button class="btn btn-sm btn-outline-secondary">Filter</button>
            @if($filterBidang || $q)
              <a href="{{ route('admin.dashboard', ['date'=>$date]) }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            @endif
          </form>
        </div>

        <div class="border rounded overflow-auto" style="max-height: 28rem;">
          @if($belumAbsenCount === 0)
            <div class="p-3 text-body-secondary">Semua pegawai sudah absen.</div>
          @else
            <ul class="list-group list-group-flush">
              @foreach($belumAbsen as $u)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <div>
                    <div class="fw-medium">{{ $u->nama }}</div>
                    @if($u->bidang)<div class="small text-body-secondary">{{ $u->bidang }}</div>@endif
                  </div>
                  <span class="badge rounded-pill bg-secondary-subtle text-secondary">Belum</span>
                </li>
              @endforeach
            </ul>
            @if($belumAbsenCount > $belumAbsen->count())
              <div class="p-2 small text-body-secondary">Dan {{ $belumAbsenCount - $belumAbsen->count() }} lainnya…</div>
            @endif
          @endif
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-6">
      <div class="app-card p-3 h-100">
        <h6 class="fw-bold mb-2">Ringkasan per Bidang ({{ $date }})</h6>
        @forelse($byBidang as $b)
          <div class="mb-3">
            <div class="d-flex justify-content-between small">
              <strong>{{ $b['bidang'] }}</strong>
              <span class="text-body-secondary">{{ $b['hadir'] }}/{{ $b['total'] }} ({{ $b['rate'] }}%)</span>
            </div>
            <div class="progress mt-2" style="height:8px;">
              <div class="progress-bar bg-primary" style="width: {{ $b['rate'] }}%"></div>
            </div>
          </div>
        @empty
          <div class="text-body-secondary">Tidak ada data.</div>
        @endforelse
      </div>
    </div>
  </div>
</div>
@endsection
