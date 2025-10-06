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
      <p class="text-body-secondary mb-0">Ringkasan absensi untuk tanggal: <strong>{{ \Carbon\Carbon::parse($date)->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</strong></p>
    </div>
    <form method="get" class="d-flex align-items-center gap-2">
      <input type="date" name="date" value="{{ $date }}" class="form-control" style="width: 180px;">
      <button class="btn btn-primary">Terapkan</button>
      <a class="btn btn-outline-secondary" href="{{ route('admin.dashboard') }}">Hari Ini</a>
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
        <a href="{{ route('admin.absensi.index', ['from'=>$date,'to'=>$date,'status'=>'Tugas Luar']) }}" class="text-decoration-none text-reset h-100">
            <div class="app-card p-3 d-flex flex-column h-100">
                <div class="d-flex align-items-center">
                    <div class="bg-secondary-subtle text-secondary p-2 rounded-3 me-2">
                        <i class="bi bi-briefcase-fill fs-5"></i>
                    </div>
                    <div class="fs-2 fw-bold">{{ $tugas_luar }}</div>
                </div>
                <div class="small text-body-secondary mt-auto">Tugas Luar</div>
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
    <div class="col-12 col-lg-7">
      <div class="app-card p-3 h-100 d-flex flex-column">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h6 class="fw-bold mb-0">Log Absensi Terbaru</h6>
          <a href="{{ route('admin.absensi.index', ['from'=>$date,'to'=>$date]) }}" class="btn btn-sm btn-outline-secondary">Lihat semua</a>
        </div>
        <div class="table-responsive flex-grow-1">
          <table class="table align-middle mb-0">
            <thead>
              <tr>
                <th>Nama Pegawai</th>
                <th style="width:120px;">Status</th>
                <th style="width:120px;" class="text-center">Jam Masuk</th>
                <th>Alasan</th>
              </tr>
            </thead>
            <tbody>
              @forelse($logTerbaru as $l)
                <tr>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      @php $foto = $l->user->foto ? asset('storage/'.$l->user->foto) : asset('img/default-avatar.jpg'); @endphp
                      <img src="{{ $foto }}" class="avatar-sm rounded-circle" alt="avatar">
                      <span class="fw-medium">{{ $l->user->nama ?? '-' }}</span>
                    </div>
                  </td>
                  <td>
                    @php $badge = $badgeStyles($l->status); @endphp
                    <span class="badge rounded-pill {{ $badge['class'] }}" style="{{ $badge['style'] }}">{{ strtoupper($l->status) }}</span>
                  </td>
                  <td class="text-center">{{ $l->jam ? \Carbon\Carbon::parse($l->jam)->format('H:i') : '-' }}</td>
                  <td class="text-body-secondary">{{ $l->alasan ?: '-' }}</td>
                </tr>
              @empty
                <tr><td colspan="4" class="text-center text-body-secondary py-4">Belum ada data absensi.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
    
    {{-- Pegawai yang Belum Absen --}}
    <div class="col-12 col-lg-5">
      <div class="app-card p-3 h-100 d-flex flex-column">
        <h6 class="fw-bold mb-2">Pegawai Belum Absen ({{ $belumAbsenCount }})</h6>
        <div class="overflow-auto flex-grow-1">
          @if($belumAbsen->isEmpty())
            <div class="p-3 text-center text-body-secondary">Semua pegawai sudah melakukan absensi. Bagus!</div>
          @else
            <ul class="list-group list-group-flush">
              @foreach($belumAbsen as $u)
                <li class="list-group-item d-flex align-items-center gap-2">
                   @php $foto = $u->foto ? asset('storage/'.$u->foto) : asset('img/default-avatar.jpg'); @endphp
                   <img src="{{ $foto }}" class="avatar-sm rounded-circle" alt="avatar">
                  <div>
                    <div class="fw-medium">{{ $u->nama }}</div>
                    @if($u->bidang)<div class="small text-body-secondary">{{ $u->bidang }}</div>@endif
                  </div>
                </li>
              @endforeach
            </ul>
          @endif
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