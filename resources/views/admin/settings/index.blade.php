@extends('layouts.admin')
@section('title','Pengaturan')

@section('content')
<div class="container has-bottom-nav" style="max-width:1100px">
  <h1 class="h4 fw-bold mb-3">Pengaturan</h1>

  {{-- Tabs --}}
  <ul class="nav nav-pills gap-2 mb-3">
    <li class="nav-item">
      <a class="nav-link {{ ($tab==='account') ? 'active' : '' }}"
         href="{{ route('admin.settings.index',['tab'=>'account']) }}">
        <i class="bi bi-person me-1"></i> Akun
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link {{ ($tab!=='account') ? 'active' : '' }}"
         href="{{ route('admin.settings.index',['tab'=>'app']) }}">
        <i class="bi bi-gear me-1"></i> Pengaturan Aplikasi
      </a>
    </li>
  </ul>

  {{-- =================== TAB: AKUN =================== --}}
  @if($tab === 'account')
    {{-- Header profil --}}
    <div class="app-card p-3 mb-3">
      <div class="d-flex align-items-center">
        <div class="me-3">
          @php $foto = $user->foto ? asset('storage/'.$user->foto) : asset('img/default-avatar.jpg'); @endphp
          <img src="{{ $foto }}" class="rounded-circle" width="88" height="88" style="object-fit:cover" alt="avatar">
        </div>

        <div class="flex-grow-1">
          <h5 class="mb-1 d-flex align-items-center gap-2">
            {{ $user->nama }}
            <span class="badge text-bg-primary">ADMIN</span>
          </h5>
          <div class="text-body-secondary small">{{ $user->jabatan ?? '-' }}</div>
          <div class="text-body-secondary small">{{ $user->bidang ?? '-' }}</div>
        </div>

        <div class="d-none d-md-flex gap-2">
          <form method="post" action="{{ route('account.photo') }}" enctype="multipart/form-data">
            @csrf
            <label class="btn btn-success mb-0">
              <i class="bi bi-image me-1"></i> Ubah Foto
              <input type="file" name="foto" class="d-none" onchange="this.form.submit()">
            </label>
          </form>
          @if($user->foto)
          <form method="post" action="{{ route('account.photo.delete') }}">
            @csrf @method('delete')
            <button class="btn btn-danger"><i class="bi bi-trash me-1"></i> Hapus Foto</button>
          </form>
          @endif
        </div>
      </div>

      {{-- versi mobile tombol foto --}}
      <div class="d-flex d-md-none gap-2 mt-3">
        <form method="post" action="{{ route('account.photo') }}" enctype="multipart/form-data" class="flex-fill">
          @csrf
          <label class="btn btn-success w-100 mb-0">
            <i class="bi bi-image me-1"></i> Ubah Foto
            <input type="file" name="foto" class="d-none" onchange="this.form.submit()">
          </label>
        </form>
        @if($user->foto)
        <form method="post" action="{{ route('account.photo.delete') }}" class="flex-fill">
          @csrf @method('delete')
          <button class="btn btn-danger w-100"><i class="bi bi-trash me-1"></i> Hapus</button>
        </form>
        @endif
      </div>
    </div>

    {{-- Informasi akun --}}
    <div class="app-card p-3 mb-3">
      <h6 class="fw-bold mb-3">Informasi Akun</h6>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Nama</label>
          <input class="form-control" value="{{ $user->nama }}" readonly>
        </div>
        <div class="col-md-6">
          <label class="form-label">Username</label>
          <input class="form-control" value="{{ $user->username }}" readonly>
        </div>
        <div class="col-md-6">
          <label class="form-label">Jabatan</label>
          <input class="form-control" value="{{ $user->jabatan }}" readonly>
        </div>
        <div class="col-md-6">
          <label class="form-label">Bidang</label>
          <input class="form-control" value="{{ $user->bidang }}" readonly>
        </div>
      </div>
    </div>
  {{-- =================== LOGOUT =================== --}}
    <div class="text-center my-4">
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="btn btn-outline-danger px-4">
          <i class="bi bi-box-arrow-right me-1"></i> Logout
        </button>
      </form>
    </div>
  </div>

  {{-- =================== TAB: PENGATURAN APLIKASI =================== --}}
  @else
    @if(session('ok'))
      <div class="alert alert-success">{{ session('ok') }}</div>
    @endif
    @if($errors->any())
      <div class="alert alert-danger mb-3">
        <ul class="mb-0">
          @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    <form method="post" action="{{ route('admin.settings.save') }}" class="app-card p-3">
      @csrf

      <h6 class="fw-bold mb-2">Poin</h6>
      <div class="row g-2 mb-3">
        @foreach(\App\Models\Absensi::getStatuses() as $key=>$label)
          <div class="col-6 col-md-2">
            <label class="form-label small text-body-secondary">{{ $label }}</label>
            <input type="number" class="form-control" name="poin[{{ $key }}]" value="{{ $poin[$key] ?? 0 }}">
          </div>
        @endforeach
      </div>

      <h6 class="fw-bold mb-2">Lokasi</h6>
      <div class="row g-2 mb-3">
        <div class="col-12 col-md-4">
          <label class="form-label small text-body-secondary">Latitude</label>
          <input type="number" step="any" class="form-control" name="lokasi[lat]" value="{{ $lokasi['lat'] }}">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label small text-body-secondary">Longitude</label>
          <input type="number" step="any" class="form-control" name="lokasi[lng]" value="{{ $lokasi['lng'] }}">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label small text-body-secondary">Radius (meter)</label>
          <input type="number" class="form-control" name="lokasi[radius]" value="{{ $lokasi['radius'] }}">
        </div>
      </div>

      <h6 class="fw-bold mb-2">Jam</h6>
      <div class="row g-2 mb-4">
        <div class="col-12 col-md-4">
          <label class="form-label small text-body-secondary">Batas Hadir</label>
          <input type="time" step="1" class="form-control" name="jam[batas_hadir]" value="{{ $jam['batas_hadir'] }}">
        </div>
      </div>

      <div class="d-flex gap-2">
        <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan</button>
        <a href="{{ route('admin.settings.index',['tab'=>'account']) }}" class="btn btn-outline-secondary">Kembali</a>
      </div>
    </form>
  @endif

  <div class="my-3"></div>
</div>
@endsection