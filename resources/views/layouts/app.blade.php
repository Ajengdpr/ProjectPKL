<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('title','Absensi')</title>

  {{-- Bootstrap 5 & Icons (CDN biar cepat) --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  {{-- Google Font --}}
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  {{-- CSS kustom --}}
  <style>
    :root{
      --brand:#2f5cff;         /* biru */
      --brand-900:#1f3fb6;
      --bg:#f5f7fb;
      --card:#ffffffcc;        /* 80% => glass */
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
  </style>

  @stack('head')
</head>
<body>

  {{-- Topbar (tampilkan saat sudah login) --}}
  @auth
  @php
    $u = auth()->user();
    $avatar = $u->foto ? asset('storage/'.$u->foto) : asset('img/default-avatar.jpg');
    $notifCount = 3; // nanti diganti query notifikasi asli
  @endphp

  <nav class="navbar navbar-expand-lg navbar-dark" style="background:linear-gradient(90deg,var(--brand),var(--brand-900))">
    <div class="container">
      <a class="navbar-brand" href="{{ route('dashboard') }}">ABSENSI</a>
      <div class="ms-auto d-flex align-items-center gap-3">

        {{-- Notifikasi --}}
        <a href="{{ route('notifications') }}" class="position-relative text-white fs-5">
          <i class="bi bi-bell"></i>
          @if($notifCount > 0)
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
            {{ $notifCount }}
          </span>
          @endif
        </a>

        {{-- Info User --}}
        <div class="d-flex align-items-center gap-2 text-white">
          <div class="text-end d-none d-md-block">
            <div class="fw-semibold">{{ \Illuminate\Support\Str::title($u->nama) }}</div>
            <div class="small text-white-50">{{ \Illuminate\Support\Str::title($u->jabatan ?? $u->bidang) }}</div>
          </div>
          <a href="{{ route('account') }}">
            <img src="{{ $avatar }}" alt="avatar"
                 class="rounded-circle border border-light-subtle"
                 style="width:36px;height:36px;object-fit:cover;">
          </a>
        </div>

      </div>
    </div>
  </nav>
  @endauth

  <main class="{{ request()->routeIs('login') ? 'p-0' : 'py-4' }}">
    @yield('content')
  </main>

{{-- ... kode lain sebelum </body> ... --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  {{-- AWAL: Tambahkan Firebase SDK --}}
  <script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-app-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-database-compat.js"></script>
  <script>
    // GANTI DENGAN KONFIGURASI FIREBASE DARI PROYEK ANDA (Langkah 1.4)
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

    // Inisialisasi Firebase
    firebase.initializeApp(firebaseConfig);
    const database = firebase.database();
  </script>
  {{-- AKHIR: Tambahkan Firebase SDK --}}

  @stack('scripts')
</body>
</html>