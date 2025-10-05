<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('title','Absensi')</title>

  {{-- Bootstrap 5 & Icons --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  {{-- Google Font --}}
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  {{-- CSS kustom --}}
  <style>
    :root{
      --brand:#2f5cff;
      --brand-900:#1f3fb6;
      --bg:#f5f7fb;
      --card:#ffffffcc;
      --shadow:0 10px 25px rgba(30,35,90,.1);
      --radius:16px;
    }
    *{font-family:Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif}
    body{background:var(--bg)}
    .navbar-brand{letter-spacing:.5px;font-weight:700}
    .app-card{background:var(--card); backdrop-filter: blur(8px); border:1px solid rgba(255,255,255,.5); border-radius:var(--radius); box-shadow:var(--shadow)}
    .btn-brand{background:var(--brand); border-color:var(--brand)}
    .btn-brand:hover{background:var(--brand-900); border-color:var(--brand-900)}
    .tile{
      border-radius:18px; padding:22px; color:#fff; display:flex;
      gap:14px; align-items:center; justify-content:center; box-shadow:var(--shadow);
      transition:transform .12s ease, box-shadow .12s ease;
    }
    .tile:hover{transform:translateY(-2px); box-shadow:0 14px 30px rgba(0,0,0,.12)}
    .tile .bi{font-size:22px}
    .tile h6{margin:0; font-weight:700; letter-spacing:.4px}
    .tile.cyan{background:#18b6d8}
    .tile.yellow{background:#efb622}
    .tile.green{background:#21a365}
    .tile.gray{background:#6b7785}
    .tile.red{background:#de3d3d}
    .tile.dark{background:#1f2533}

    .bottom-nav{
      position:sticky; bottom:0; left:0; right:0; backdrop-filter:blur(8px);
      background:#ffffffcc; border-top:1px solid #e9edf5; z-index:10
    }
    .bottom-nav .nav-link{color:#4b5563; font-weight:600}
    .bottom-nav .nav-link.active{color:var(--brand)}
    table thead th{font-weight:700}

    .form-control:disabled,
    .form-control[readonly] {
      background: #f7f9fc;
      color: #111827;
      opacity: 1;
    }

    /* Sidebar */
    .app-sidebar {
      position: fixed; top: 0; left: 0; width: 260px; height: 100%;
      background-color: #fff; z-index: 1050;
      transform: translateX(-100%);
      transition: transform 0.3s ease-in-out;
      box-shadow: 0 8px 22px rgba(16,24,40,.08);
      overflow-y: auto;
    }
    .app-sidebar.show { transform: translateX(0); }
    .sidebar-header {
      display: flex; align-items: center; justify-content: space-between;
      padding: 1rem 1.25rem; border-bottom: 1px solid #e9edf5;
    }
    .sidebar-nav { padding: 1rem; }
    .sidebar-nav .nav-link {
      display: flex; align-items: center; gap: 0.75rem;
      padding: 0.75rem 1rem; margin-bottom: 0.25rem;
      border-radius: 8px; color: #334155; font-weight: 500;
    }
    .sidebar-nav .nav-link:hover { background-color: #f1f5f9; }
    .sidebar-nav .nav-link.active {
      background-color: #e0e7ff; color: #4338ca; font-weight: 600;
    }
    .sidebar-nav .nav-link .bi { font-size: 1.2rem; }

    /* Overlay */
    .sidebar-overlay {
      position: fixed; top: 0; left: 0; width: 100%; height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 1040;
      display: none;
    }
    .sidebar-overlay.show { display: block; }

    /* Desktop sidebar */
    @media (min-width: 992px) {
      body.sidebar-toggled {
        padding-left: 260px;
      }
      body.sidebar-toggled .app-sidebar { transform: translateX(0); }
      body.sidebar-toggled .sidebar-overlay { display: none; }
    }
  </style>

  @stack('head')
</head>
<body>

@auth
@php
    $u = auth()->user();
    $isAdmin = ($u->role ?? 'user') === 'admin';
    $avatar = $u->foto ? asset('storage/'.$u->foto) : asset('img/default-avatar.jpg');
    $profileRouteName = $isAdmin ? 'admin.settings.index' : 'account';
    $profileRouteParams = $isAdmin ? ['tab' => 'account'] : [];
    $unread = (method_exists($u, 'unreadNotifications') && \Illuminate\Support\Facades\Schema::hasTable('notifications'))
        ? $u->unreadNotifications()->count()
        : 0;
@endphp

{{-- Navbar --}}
<nav class="navbar navbar-expand-lg navbar-dark sticky-top" style="background:linear-gradient(90deg,var(--brand),var(--brand-900))">
  <div class="container d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-2">
      <button class="btn btn-primary" id="sidebarToggle">
        <i class="bi bi-list"></i>
      </button>
      <a class="navbar-brand m-0" href="{{ $isAdmin ? route('admin.dashboard') : route('dashboard') }}">E-Absensi</a>
    </div>

    <div class="d-flex align-items-center gap-3">
      <a href="{{ route('notifications.index') }}" class="position-relative text-white fs-5" title="Notifikasi">
        <i class="bi bi-bell"></i>
        @if($unread > 0)
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $unread }}</span>
        @endif
      </a>
      <div class="d-flex align-items-center gap-2 text-white">
        <div class="text-end d-none d-md-block">
          <div class="fw-semibold">{{ \Illuminate\Support\Str::title($u->nama) }}</div>
          <div class="small text-white-50">{{ \Illuminate\Support\Str::title($u->jabatan ?? $u->bidang) }}</div>
        </div>
        <a href="{{ route($profileRouteName, $profileRouteParams) }}">
          <img src="{{ $avatar }}" alt="avatar" class="rounded-circle border border-light-subtle" style="width:36px;height:36px;object-fit:cover;">
        </a>
      </div>
    </div>
  </div>
</nav>

{{-- Sidebar --}}
<div class="app-sidebar" id="appSidebar">
  <div class="sidebar-header d-flex align-items-center justify-content-between">
  <!-- Logo + Marquee Wrapper -->
  <div class="d-flex align-items-center overflow-hidden" style="flex:1; gap:0.5rem;">
  <img src="{{ asset('img/logo_provinsi.png') }}" alt="Logo Provinsi" style="width:40px; height:30px; object-fit:contain; flex-shrink:0;">
  <div class="marquee-wrapper flex-grow-1">
    <h4 class="marquee-text text-primary fw-bold mb-0" style="font-size:1.2rem;">
      Dinas Lingkungan Hidup Provinsi Kalimantan Selatan
    </h4>
  </div>
</div>

  <!-- Tombol tutup sidebar (hanya mobile) -->
  <button class="btn btn-sm btn-light d-lg-none" id="sidebarClose">
    <i class="bi bi-arrow-left"></i>
  </button>
</div>

<style>
.marquee-wrapper {
  overflow: hidden;
  white-space: nowrap;
}

.marquee-text {
  display: inline-block;
  padding-left: 100%;
  will-change: transform;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const marquee = document.querySelector('.marquee-text');
  const wrapper = document.querySelector('.marquee-wrapper');
  
  function animateMarquee() {
    const textWidth = marquee.offsetWidth;
    const wrapperWidth = wrapper.offsetWidth;
    let start = wrapperWidth;
    
    function step() {
      start -= 0.5; // kecepatan scroll
      if (start < -textWidth) start = wrapperWidth;
      marquee.style.transform = translateX(${start}px);
      requestAnimationFrame(step);
    }
    
    step();
  }
  
  animateMarquee();
});
</script>

  <ul class="nav flex-column sidebar-nav">
    @if($isAdmin)
      <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.dashboard')?'active':'' }}" href="{{ route('admin.dashboard') }}"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a></li>
      <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.users.*')?'active':'' }}" href="{{ route('admin.users.index') }}"><i class="bi bi-people-fill"></i> Pegawai</a></li>
      <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.absensi.*')?'active':'' }}" href="{{ route('admin.absensi.index') }}"><i class="bi bi-calendar-check-fill"></i> Absensi</a></li>
      <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.settings.*')?'active':'' }}" href="{{ route('admin.settings.index') }}"><i class="bi bi-gear-fill"></i> Pengaturan</a></li>
    @else
      {{-- Menu untuk User Biasa --}}
      <li class="nav-item"><a class="nav-link {{ request()->routeIs('dashboard')?'active':'' }}" href="{{ route('dashboard') }}"><i class="bi bi-house-door"></i> Home</a></li>
      <li class="nav-item"><a class="nav-link {{ request()->routeIs('statistik')?'active':'' }}" href="{{ route('statistik') }}"><i class="bi bi-graph-up"></i> Statistik</a></li>
      <li class="nav-item"><a class="nav-link {{ request()->routeIs('account')?'active':'' }}" href="{{ route('account') }}"><i class="bi bi-person"></i> Account</a></li>
    @endif

    {{-- Menu yang sama untuk Admin & User --}}
    
    <li class="nav-item">
      <a class="nav-link {{ request()->routeIs('notifications.index')?'active':'' }}" href="{{ route('notifications.index') }}"><i class="bi bi-bell"></i> Notifikasi</a>
    </li>
    <li class="nav-item">
      <a class="nav-link text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        <i class="bi bi-box-arrow-right"></i> Logout
      </a>
    </li>
  </ul>
</div>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
@endauth

<main class="{{ request()->routeIs('login') ? 'p-0' : 'py-4' }}">
  @yield('content')
</main>

{{-- Form Logout (tersembunyi) --}}
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
  @csrf
</form>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-database-compat.js"></script>
<script>
const firebaseConfig = {
  apiKey: "AIzaSyBlRJlDMXduk2BRjW0U8tkhGeFb6YtGZkI",
  authDomain: "project-pkl-b57bf.firebaseapp.com",
  databaseURL: "https://project-pkl-b57bf-default-rtdb.asia-southeast1.firebasedatabase.app",
  projectId: "project-pkl-b57bf",
  storageBucket: "project-pkl-b57bf.firebasestorage.app",
  messagingSenderId: "161424256692",
  appId: "1:161424256692:web:e7d89cfcfab2cf4ff331e1",
  measurementId: "G-QHQE9C1XD8"
};
firebase.initializeApp(firebaseConfig);
const database = firebase.database();
</script>

@auth
<script>
document.addEventListener('DOMContentLoaded', function () {
  const sidebar = document.getElementById('appSidebar');
  const overlay = document.getElementById('sidebarOverlay');
  const toggleBtn = document.getElementById('sidebarToggle');
  const closeBtn = document.getElementById('sidebarClose');
  const body = document.body;

  // buka sidebar
  toggleBtn.addEventListener('click', () => {
    sidebar.classList.add('show');
    overlay.classList.add('show');
  });

  // tutup sidebar
  function closeSidebar(){
    sidebar.classList.remove('show');
    overlay.classList.remove('show');
  }

  overlay.addEventListener('click', closeSidebar);
  if(closeBtn) closeBtn.addEventListener('click', closeSidebar);

  // desktop selalu sidebar terbuka
  if(window.innerWidth >= 992){
    body.classList.add('sidebar-toggled');
  }
});
</script>
@endauth

@stack('scripts')
</body>
</html>