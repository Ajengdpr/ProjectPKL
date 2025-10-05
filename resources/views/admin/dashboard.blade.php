@extends('layouts.admin')
@section('title','Dashboard Admin')

@once
<style>
  /* Ukuran avatar kecil untuk daftar & log */
  .avatar-sm { width: 32px; height: 32px; object-fit: cover; }
  /* Memastikan kartu di baris yang sama memiliki tinggi yang seragam */
  .row.match-height > [class*="col-"] { display: flex; flex-direction: column; }
  .row.match-height > [class*="col-"] > .app-card { flex-grow: 1; }
</style>
@endonce

@section('content')
@php
  // Fungsi untuk class badge status
  $badgeStyles = function($status) {
      $safeStatus = strtolower(trim($status ?? ''));
      return match($safeStatus) {
          'hadir'       => ['class' => 'bg-success-subtle', 'style' => 'color: #146c43 !important;'],
          'terlambat'   => ['class' => 'bg-warning-subtle', 'style' => 'color: #e59400 !important; font-weight: 600;'],
          'sakit'       => ['class' => 'bg-info-subtle',    'style' => 'color: #087990 !important;'],
          'izin'        => ['class' => 'bg-primary-subtle', 'style' => 'color: #0a58ca !important;'],
          'cuti'        => ['class' => 'bg-warning-subtle', 'style' => 'color: #e59400 !important;'],
          'tugas luar'  => ['class' => 'bg-secondary-subtle', 'style' => 'color: #41464b !important;'],
          'alpha'       => ['class' => 'bg-danger-subtle',  'style' => 'color: #b02a37 !important; font-weight: 600;'],
          default       => ['class' => 'bg-light',          'style' => 'color: #000 !important;']
      };
  };
@endphp

<div class="container" style="max-width:1100px">

  {{-- HEADER + FILTER BAR --}}
  <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-md-between gap-2 mb-3">
    <div>
      <h1 class="h3 fw-bold mb-0">Dashboard</h1>
      <p class="text-body-secondary mb-0">Ringkasan absensi untuk tanggal: 
        <strong>{{ \Carbon\Carbon::parse($date)->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</strong>
      </p>
    </div>
    <form method="get" class="d-flex align-items-center gap-2">
      <input type="date" name="date" value="{{ $date }}" class="form-control" style="width: 180px;">
      <button class="btn btn-primary">Terapkan</button>
      <div class="dropdown">
        <button class="btn btn-outline-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-three-dots-vertical"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">Tampilkan Hari Ini</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="{{ route('admin.absensi.export.csv', ['from'=>$date,'to'=>$date]) }}">
            <i class="bi bi-download me-2"></i>Export CSV</a></li>
        </ul>
      </div>
    </form>
  </div>

  {{-- STAT CARDS --}}
  <div class="row g-3 match-height">
    <div class="col">
      <a href="{{ route('admin.users.index') }}" class="text-decoration-none text-reset h-100">
        <div class="app-card p-3 d-flex flex-column h-100">
          <div class="d-flex align-items-center">
            <div class="bg-primary-subtle text-primary p-2 rounded-3 me-2">
              <i class="bi bi-people-fill fs-5"></i>
            </div>
            <div class="fs-2 fw-bold">{{ $totalPegawai }}</div>
          </div>
          <div class="small text-body-secondary mt-auto">Total Pegawai</div>
        </div>
      </a>
    </div>
    <div class="col">
      <a href="{{ route('admin.absensi.index', ['from'=>$date,'to'=>$date,'status'=>'hadir']) }}" class="text-decoration-none text-reset h-100">
        <div class="app-card p-3 d-flex flex-column h-100">
          <div class="d-flex align-items-center">
            <div class="bg-success-subtle text-success p-2 rounded-3 me-2">
              <i class="bi bi-check-circle-fill fs-5"></i>
            </div>
            <div class="fs-2 fw-bold">{{ $hadir }}</div>
          </div>
          <div class="small text-body-secondary mt-auto">Hadir</div>
        </div>
      </a>
    </div>
    <div class="col">
      <a href="{{ route('admin.absensi.index', ['from'=>$date,'to'=>$date,'status'=>'terlambat']) }}" class="text-decoration-none text-reset h-100">
        <div class="app-card p-3 d-flex flex-column h-100">
          <div class="d-flex align-items-center">
            <div class="bg-warning-subtle text-warning p-2 rounded-3 me-2">
              <i class="bi bi-clock-fill fs-5"></i>
            </div>
            <div class="fs-2 fw-bold">{{ $terlambat }}</div>
          </div>
          <div class="small text-body-secondary mt-auto">Terlambat</div>
        </div>
      </a>
    </div>
    <div class="col">
      <a href="{{ route('admin.absensi.index', ['from'=>$date,'to'=>$date,'status'=>'sakit']) }}" class="text-decoration-none text-reset h-100">
        <div class="app-card p-3 d-flex flex-column h-100">
          <div class="d-flex align-items-center">
            <div class="bg-info-subtle text-info p-2 rounded-3 me-2">
              <i class="bi bi-heart-pulse-fill fs-5"></i>
            </div>
            <div class="fs-2 fw-bold">{{ $sakit }}</div>
          </div>
          <div class="small text-body-secondary mt-auto">Sakit</div>
        </div>
      </a>
    </div>
    <div class="col">
      <a href="{{ route('admin.absensi.index', ['from'=>$date,'to'=>$date,'status'=>'izin']) }}" class="text-decoration-none text-reset h-100">
        <div class="app-card p-3 d-flex flex-column h-100">
          <div class="d-flex align-items-center">
            <div class="bg-primary-subtle text-primary p-2 rounded-3 me-2">
              <i class="bi bi-card-list fs-5"></i>
            </div>
            <div class="fs-2 fw-bold">{{ $izin }}</div>
          </div>
          <div class="small text-body-secondary mt-auto">Izin</div>
        </div>
      </a>
    </div>

    {{-- Card Tugas Luar --}}
    <div class="col">
      <a href="{{ route('admin.absensi.index', ['from'=>$date,'to'=>$date,'status'=>'tugas luar']) }}" class="text-decoration-none text-reset h-100">
        <div class="app-card p-3 d-flex flex-column h-100">
          <div class="d-flex align-items-center">
            <div class="bg-secondary-subtle text-secondary p-2 rounded-3 me-2">
              <i class="bi bi-briefcase-fill fs-5"></i>
            </div>
            <div class="fs-2 fw-bold">{{ $tugasLuar }}</div>
          </div>
          <div class="small text-body-secondary mt-auto">Tugas Luar</div>
        </div>
      </a>
    </div>

    <div class="col">
      <a href="{{ route('admin.absensi.index', ['from'=>$date,'to'=>$date,'status'=>'cuti']) }}" class="text-decoration-none text-reset h-100">
        <div class="app-card p-3 d-flex flex-column h-100">
          <div class="d-flex align-items-center">
            <div class="p-2 rounded-3 me-2" style="background-color: #f7e6d5; color: #fd7e14;">
              <i class="bi bi-calendar-x-fill fs-5"></i>
            </div>
            <div class="fs-2 fw-bold" style="color: #fd7e14;">{{ $cuti }}</div>
          </div>
          <div class="small text-body-secondary mt-auto">Cuti</div>
        </div>
      </a>
    </div>
    <div class="col">
      <a href="{{ route('admin.absensi.index', ['from'=>$date,'to'=>$date,'status'=>'alpha']) }}" class="text-decoration-none text-reset h-100">
        <div class="app-card p-3 d-flex flex-column h-100">
          <div class="d-flex align-items-center">
            <div class="bg-danger-subtle text-danger p-2 rounded-3 me-2">
              <i class="bi bi-x-circle-fill fs-5"></i>
            </div>
            <div class="fs-2 fw-bold">{{ $alpha }}</div>
          </div>
          <div class="small text-body-secondary mt-auto">Tanpa Keterangan</div>
        </div>
      </a>
    </div>
  </div>

<div class="row g-3 mt-1 match-height">
  {{-- Log Absensi Terbaru --}}
  <div class="col-md-8 d-flex flex-column" id="log-absensi">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-bold">Log Absensi Terbaru</span>
        <span class="badge bg-primary rounded-pill">
          {{ $logTerbaru->count() }} sudah absen
        </span>
      </div>
      <div class="card-body p-0">
        <ul class="list-group list-group-flush">
          @forelse ($logTerbaru as $log)
            <li class="list-group-item d-flex justify-content-between">
              <span>{{ $log->user->nama }}</span>
              <span class="badge {{ $badgeStyles($log->status)['class'] }}" 
                    style="{{ $badgeStyles($log->status)['style'] }}">
                {{ ucfirst($log->status) }}
              </span>
            </li>
          @empty
            <li class="list-group-item text-muted">Belum ada data</li>
          @endforelse
        </ul>
      </div>
      <div class="card-footer d-flex justify-content-between align-items-center small text-muted">
        <span>
          Halaman {{ $logTerbaru->currentPage() }} dari {{ $logTerbaru->lastPage() }}
        </span>
        <div>
          {{-- Tombol panah --}}
          @if ($logTerbaru->onFirstPage())
            <span class="text-secondary me-2">&laquo;</span>
          @else
            <a href="{{ $logTerbaru->previousPageUrl() }}#log-absensi" class="me-2">&laquo;</a>
          @endif

          @if ($logTerbaru->hasMorePages())
            <a href="{{ $logTerbaru->nextPageUrl() }}#log-absensi">&raquo;</a>
          @else
            <span class="text-secondary">&raquo;</span>
          @endif
        </div>
      </div>

    </div>
  </div>

  {{-- Pegawai yang Belum Absen --}}
  <div class="col-md-4 d-flex flex-column" id="belum-absen">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-bold">Pegawai Belum Absen</span>
        <span class="badge bg-danger rounded-pill">
          {{ $belumAbsen->count() }} belum absen
        </span>
      </div>
      <div class="card-body p-0">
        <ul class="list-group list-group-flush">
          @forelse ($belumAbsen as $pegawai)
            <li class="list-group-item">{{ $pegawai->nama }}</li>
          @empty
            <li class="list-group-item text-muted">Semua sudah absen 🎉</li>
          @endforelse
        </ul>
      </div>
      <div class="card-footer d-flex justify-content-between align-items-center small text-muted">
        <span>
          Halaman {{ $belumAbsen->currentPage() }} dari {{ $belumAbsen->lastPage() }}
        </span>
        <div>
          @if ($belumAbsen->onFirstPage())
            <span class="text-secondary me-2">&laquo;</span>
          @else
            <a href="{{ $belumAbsen->previousPageUrl() }}#belum-absen" class="me-2">&laquo;</a>
          @endif

          @if ($belumAbsen->hasMorePages())
            <a href="{{ $belumAbsen->nextPageUrl() }}#belum-absen">&raquo;</a>
          @else
            <span class="text-secondary">&raquo;</span>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

  <div class="row g-3 mt-1">
    {{-- Ringkasan per Bidang --}}
    <div class="col-12">
      <div class="app-card p-3 h-100">
        <h6 class="fw-bold mb-3">Ringkasan Kehadiran per Bidang</h6>
        <div class="row g-3">
          @forelse($byBidang as $b)
          <div class="col-12 col-md-6">
            <div class="d-flex justify-content-between small mb-1">
              <strong class="text-dark">{{ $b['bidang'] }}</strong>
              <span class="text-body-secondary">{{ $b['hadir_total'] }} dari {{ $b['total'] }} pegawai hadir</span>
            </div>
            <div class="progress" style="height: 10px;" title="Total Kehadiran: {{ $b['hadir_total_rate'] }}%">
              <div class="progress-bar bg-success" role="progressbar" style="width: {{ $b['hadir_rate'] }}%" title="Hadir: {{ $b['hadir_rate'] }}%"></div>
              <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $b['terlambat_rate'] }}%" title="Terlambat: {{ $b['terlambat_rate'] }}%"></div>
              <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $b['alpha_rate'] }}%" title="Tanpa Keterangan: {{ $b['alpha_rate'] }}%"></div>
            </div>
          </div>
          @empty
          <div class="col-12 text-body-secondary">Tidak ada data untuk ditampilkan.</div>
          @endforelse
        </div>
      </div>
    </div>
  </div>
</div>
@endsection