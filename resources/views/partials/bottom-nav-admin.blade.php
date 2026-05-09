@php
  $is = fn($p) => request()->routeIs($p);
@endphp

<nav class="bottom-nav">
  <div class="container-fluid">
    <div class="d-flex justify-content-around">
      <a class="nav-link {{ $is('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
        <i class="bi bi-grid-1x2-fill"></i>
        <span>Beranda</span>
      </a>
      <a class="nav-link {{ $is('admin.absensi.*') ? 'active' : '' }}" href="{{ route('admin.absensi.index') }}">
        <i class="bi bi-calendar-check-fill"></i>
        <span>Absensi</span>
      </a>
      <a class="nav-link {{ $is('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
        <i class="bi bi-people-fill"></i>
        <span>Pegawai</span>
      </a>
      <a class="nav-link {{ $is('admin.statistik.*') ? 'active' : '' }}" href="{{ route('admin.statistik.index') }}">
        <i class="bi bi-bar-chart-line-fill"></i>
        <span>Statistik</span>
      </a>
      <a class="nav-link {{ $is('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}">
        <i class="bi bi-gear-fill"></i>
        <span>Set</span>
      </a>
    </div>
  </div>
</nav>