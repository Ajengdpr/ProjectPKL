@extends('layouts.admin')
@section('title','Beranda Admin')

@once
<style>
  /* Ukuran avatar kecil untuk daftar & log */
  .avatar-sm { width: 32px; height: 32px; object-fit: cover; }
  /* Memastikan kartu di baris yang sama memiliki tinggi yang seragam */
  .row.match-height > [class*="col-"] { display: flex; flex-direction: column; }
  .row.match-height > [class*="col-"] > .app-card { flex-grow: 1; }

  /* Gaya untuk slider pegawai belum absen per bidang */
  .belum-absen-container {
    max-height: 300px;
    overflow-y: auto;
    padding-right: 5px;
  }
  .log-absensi-container {
    max-height: 300px;
    overflow-y: auto;
  }
  .bidang-section {
    margin-bottom: 20px;
    background: #f8f9fa;
    padding: 12px;
    border-radius: 12px;
    border: 1px solid #eee;
  }
  .bidang-title {
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--brand-900);
    text-transform: uppercase;
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e0e0e0;
    padding-bottom: 5px;
  }
  .horizontal-scroll-wrapper {
    overflow-x: auto;
    cursor: grab;
    padding-bottom: 8px;
    -webkit-overflow-scrolling: touch;
  }
  /* Scrollbar tipis untuk indikator visual */
  .horizontal-scroll-wrapper::-webkit-scrollbar {
    height: 4px;
  }
  .horizontal-scroll-wrapper::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
  }
  .horizontal-scroll-wrapper::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 10px;
  }
  .horizontal-scroll-wrapper::-webkit-scrollbar-thumb:hover {
    background: var(--brand);
  }
  .horizontal-scroll-wrapper:active {
    cursor: grabbing;
  }
  .horizontal-scroll-content {
    display: flex;
    gap: 12px;
    width: max-content;
  }
  .user-card-mini {
    flex: 0 0 auto;
    width: 85px;
    text-align: center;
  }
  .user-card-mini img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border: 2px solid #fff;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  }
  .user-card-mini .name {
    font-size: 0.7rem;
    font-weight: 600;
    margin-top: 5px;
    line-height: 1.2;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    height: 1.7rem;
  }
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

  {{-- HEADER CARD + FILTER BAR (OPSI 4) --}}
  <div class="app-card p-3 mb-4 bg-light border-0 shadow-sm">
    <div class="row g-3 align-items-center">
      <div class="col-12 col-md-auto me-auto">
        <div class="d-flex align-items-center">
          <div class="bg-white p-2 rounded-circle shadow-sm me-3 text-primary">
            <i class="bi bi-calendar3 fs-5"></i>
          </div>
          <div>
            <div class="small text-body-secondary fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.7rem;">Ringkasan Kehadiran</div>
            <h1 class="h5 fw-bold mb-0 text-dark">
              {{ \Carbon\Carbon::parse($date)->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
            </h1>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-auto">
        <form method="get" class="d-flex align-items-center gap-2 flex-nowrap">
          <input type="hidden" name="month" value="{{ $month }}">
          <div class="input-group input-group-sm shadow-sm">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-filter"></i></span>
            <input type="date" name="date" value="{{ $date }}" class="form-control border-start-0" style="width: 140px;">
          </div>
          <button class="btn btn-sm btn-primary shadow-sm px-3 text-nowrap">Terapkan</button>
          <a class="btn btn-sm btn-outline-secondary shadow-sm text-nowrap" href="{{ route('admin.dashboard') }}">Hari Ini</a>
        </form>
      </div>
    </div>
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
                    <div class="bg-warning-subtle text-warning p-2 rounded-3 me-2">
                        <i class="bi bi-calendar-x-fill fs-5"></i>
                    </div>
                    <div class="fs-2 fw-bold">{{ $cuti }}</div>
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
        <div class="table-responsive log-absensi-container flex-grow-1">
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
                      @php $foto = $l->user->foto ? asset('storage/'.$l->user->foto).'?v='.time() : asset('img/default-avatar.jpg'); @endphp
                      <img src="{{ $foto }}" class="avatar-sm rounded-circle" alt="avatar">
                      <span class="fw-medium">{{ $l->user->nama ?? '-' }}</span>
                    </div>
                  </td>
                  <td>
                    @php 
                      $statusText = $l->status;
                      $isPending = !$l->is_approved && !$l->is_rejected;
                      $isRejected = $l->is_rejected;
                      
                      if ($isRejected) {
                        $statusText = 'Tanpa Keterangan';
                      }
                      
                      $badge = $badgeStyles($statusText); 
                    @endphp
                    <span class="badge rounded-pill {{ $badge['class'] }}" style="{{ $badge['style'] }}">
                      {{ strtoupper($statusText) }}
                      @if($isPending)
                        <i class="bi bi-clock-history ms-1" title="Menunggu Persetujuan"></i>
                      @elseif($isRejected)
                        <small class="d-block text-danger" style="font-size: 0.6rem; text-transform: uppercase;">
                          ({{ $l->status }} DITOLAK)
                        </small>
                      @endif
                    </span>
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
        <div class="mb-3">
            <h6 class="fw-bold mb-0">Pegawai Belum Absen ({{ $belumAbsenCount }})</h6>
        </div>
        
        <div class="belum-absen-container flex-grow-1 pe-2">
          @if($belumAbsen->isEmpty())
            <div class="p-3 text-center text-body-secondary bg-light rounded-3 h-100 d-flex flex-column justify-content-center align-items-center">
                <i class="bi bi-person-check fs-2 d-block mb-2 text-success opacity-50"></i>
                <div>Semua pegawai sudah absen atau hari ini libur.</div>
            </div>
          @else
            @foreach($belumAbsen as $namaBidang => $users)
              <div class="bidang-section shadow-sm">
                <div class="bidang-title">
                    <span>{{ $namaBidang ?: 'TANPA BIDANG' }}</span>
                    <span class="badge bg-white text-dark border rounded-pill shadow-sm" style="font-size: 0.65rem;">{{ count($users) }} Pegawai</span>
                </div>
                
                <div class="horizontal-scroll-wrapper">
                    <div class="horizontal-scroll-content">
                        @foreach($users as $u)
                        <div class="user-card-mini">
                            @php $foto = $u->foto ? asset('storage/'.$u->foto).'?v='.time() : asset('img/default-avatar.jpg'); @endphp
                            <img src="{{ $foto }}" class="rounded-circle" alt="avatar">
                            <div class="name" title="{{ $u->nama }}">{{ $u->nama }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
              </div>
            @endforeach
          @endif
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mt-1">
    {{-- Ringkasan per Bidang --}}
    <div class="col-12 col-lg-7">
      <div class="app-card p-3 h-100">
        <h6 class="fw-bold mb-3">Ringkasan Kehadiran per Bidang</h6>
        <div class="row g-3">
            @foreach($byBidang as $b)
            <div class="col-12">
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
            @endforeach
        </div>
      </div>
    </div>

    {{-- Ranking Poin Pegawai (Desain Leaderboard) --}}
    <div class="col-12 col-lg-5">
      <div class="app-card p-3 h-100 d-flex flex-column">
        <div class="d-flex flex-column gap-3 mb-3">
          <div class="d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0">Ranking Poin Pegawai</h6>
            <a href="{{ route('admin.export.points', ['month' => $month]) }}" class="btn btn-sm btn-success shadow-sm text-nowrap">
              <i class="bi bi-download me-1"></i> Export
            </a>
          </div>
          
          <form method="get" action="{{ route('admin.dashboard') }}" class="row g-2 align-items-center">
            <input type="hidden" name="date" value="{{ $date }}">
            <div class="col">
              <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-calendar-month"></i></span>
                <input type="month" name="month" value="{{ $month }}" class="form-control border-start-0" onchange="this.form.submit()">
              </div>
            </div>
            <div class="col-auto">
                <small class="text-muted fw-semibold">{{ \Carbon\Carbon::parse($month)->locale('id')->isoFormat('MMMM YYYY') }}</small>
            </div>
          </form>
        </div>
        
        <div class="flex-grow-1 overflow-auto pe-2" style="max-height: 280px;">
          <div class="list-group list-group-flush">
            @foreach($rankingPoin as $index => $rp)
              <div class="list-group-item border-0 px-0 py-2 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                  {{-- Indikator Peringkat --}}
                  <div class="d-flex justify-content-center align-items-center fw-bold" style="width: 32px;">
                    @if($index == 0)
                      <span class="fs-4">🥇</span>
                    @elseif($index == 1)
                      <span class="fs-4">🥈</span>
                    @elseif($index == 2)
                      <span class="fs-4">🥉</span>
                    @else
                      <span class="text-body-secondary small">#{{ $index + 1 }}</span>
                    @endif
                  </div>

                  {{-- Profil Pegawai --}}
                  <div class="d-flex align-items-center gap-2">
                    @php $foto = $rp->foto ? asset('storage/'.$rp->foto).'?v='.time() : asset('img/default-avatar.jpg'); @endphp
                    <img src="{{ $foto }}" class="rounded-circle shadow-sm border border-2 border-white" style="width: 38px; height: 38px; object-fit: cover;">
                    <div>
                      <div class="fw-bold text-dark small mb-0">{{ $rp->nama }}</div>
                    </div>
                  </div>
                </div>

                {{-- Poin --}}
                <div class="text-end">
                  <span class="badge rounded-pill bg-primary-subtle text-primary fw-bold" style="font-size: 0.85rem; padding: 0.5em 1em;">
                    {{ $rp->point }} <small class="fw-normal">poin</small>
                  </span>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sliders = document.querySelectorAll('.horizontal-scroll-wrapper');
    
    sliders.forEach(slider => {
        let isDown = false;
        let startX;
        let scrollLeft;

        slider.addEventListener('mousedown', (e) => {
            isDown = true;
            slider.classList.add('active');
            startX = e.pageX - slider.offsetLeft;
            scrollLeft = slider.scrollLeft;
        });
        
        slider.addEventListener('mouseleave', () => {
            isDown = false;
            slider.classList.add('active');
        });
        
        slider.addEventListener('mouseup', () => {
            isDown = false;
            slider.classList.add('active');
        });
        
        slider.addEventListener('mousemove', (e) => {
            if(!isDown) return;
            e.preventDefault();
            const x = e.pageX - slider.offsetLeft;
            const walk = (x - startX) * 2; // Kecepatan scroll
            slider.scrollLeft = scrollLeft - walk;
        });
    });
});
</script>
@endpush
