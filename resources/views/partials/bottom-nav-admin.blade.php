@php
  $is = fn($p) => request()->routeIs($p);
@endphp

<nav class="bottom-nav mt-4">
  <div class="container">
    <ul class="nav justify-content-around py-2">
      <li class="nav-item">
        <a class="nav-link {{ $is('admin.dashboard') ? 'active' : '' }}"
           href="{{ route('admin.dashboard') }}">
          <i class="bi bi-house-door me-1"></i> Home
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ $is('admin.absensi.*') ? 'active' : '' }}"
           href="{{ route('admin.absensi.index') }}">
          <i class="bi bi-calendar-check me-1"></i> Absensi
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ $is('admin.users.*') ? 'active' : '' }}"
           href="{{ route('admin.users.index') }}">
          <i class="bi bi-people me-1"></i> Users
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ $is('admin.settings.*') ? 'active' : '' }}"
           href="{{ route('admin.settings.index') }}">
          <i class="bi bi-gear me-1"></i> Settings
        </a>
      </li>
    </ul>
  </div>
</nav>