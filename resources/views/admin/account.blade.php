@extends('layouts.admin')
@section('title','Akun Admin')

@section('content')
<div class="container has-bottom-nav" style="max-width:1100px">
  {{-- Header profil (sama feelnya dengan user, tapi beri badge ADMIN) --}}
  <div class="app-card p-3 mb-3">
    <div class="d-flex align-items-center">
      {{-- Foto --}}
      <div class="me-3">
        @php $foto = $user->foto ? asset('storage/'.$user->foto) : asset('img/default-avatar.png'); @endphp
        <img src="{{ $foto }}" class="rounded-circle" alt="avatar" width="88" height="88" style="object-fit:cover">
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
            <i class="bi bi-image me-1"></i> Change picture
            <input type="file" name="foto" class="d-none" onchange="this.form.submit()">
          </label>
        </form>
        @if($user->foto)
        <form method="post" action="{{ route('account.photo.delete') }}">
          @csrf @method('delete')
          <button class="btn btn-danger"><i class="bi bi-trash me-1"></i>Delete picture</button>
        </form>
        @endif
      </div>
    </div>

    {{-- versi mobile untuk tombol foto --}}
    <div class="d-flex d-md-none gap-2 mt-3">
      <form method="post" action="{{ route('account.photo') }}" enctype="multipart/form-data" class="flex-fill">
        @csrf
        <label class="btn btn-success w-100 mb-0">
          <i class="bi bi-image me-1"></i> Change picture
          <input type="file" name="foto" class="d-none" onchange="this.form.submit()">
        </label>
      </form>
      @if($user->foto)
      <form method="post" action="{{ route('account.photo.delete') }}" class="flex-fill">
        @csrf @method('delete')
        <button class="btn btn-danger w-100"><i class="bi bi-trash me-1"></i>Delete</button>
      </form>
      @endif
    </div>
  </div>

  {{-- Quick admin shortcuts --}}
  <div class="row g-2 mb-3">
    <div class="col-6 col-md-3">
      <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary w-100">
        <i class="bi bi-speedometer2 me-1"></i> Dashboard
      </a>
    </div>
    <div class="col-6 col-md-3">
      <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary w-100">
        <i class="bi bi-people me-1"></i> Users
      </a>
    </div>
    <div class="col-6 col-md-3">
      <a href="{{ route('admin.absensi.index') }}" class="btn btn-outline-primary w-100">
        <i class="bi bi-calendar-check me-1"></i> Absensi
      </a>
    </div>
    <div class="col-6 col-md-3">
      <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-primary w-100">
        <i class="bi bi-gear me-1"></i> Settings
      </a>
    </div>
  </div>

  {{-- Informasi Akun (readonly seperti user) --}}
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

  {{-- Logout --}}
  <form method="post" action="{{ route('logout') }}" class="text-center">
    @csrf
    <button class="btn btn-outline-danger">
      <i class="bi bi-box-arrow-right me-1"></i> Logout
    </button>
  </form>

  <div class="my-3"></div>
</div>
@endsection