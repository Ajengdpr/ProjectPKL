@extends('layouts.app')
@section('title','Profil Saya')

@section('content')
@php
  $u   = $user ?? auth()->user();
  $src = $u->foto ? asset('storage/'.$u->foto) : asset('img/default-avatar.jpg');
@endphp

<div class="container" style="max-width:980px">

  {{-- Notif --}}
  @if(session('ok')) <div class="alert alert-success">{{ session('ok') }}</div> @endif
  @if($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

  {{-- Kartu profil utama --}}
  <div class="app-card p-4 p-md-5 mb-4">
    <div class="row align-items-center g-4">

      {{-- Foto --}}
      <div class="col-md-3 text-center">
        <img src="{{ $src }}" alt="Foto {{Str::title($u->nama)}}"
             class="rounded-circle shadow"
             style="width:110px;height:110px;object-fit:cover;">
      </div>

      {{-- Detail user (read-only) --}}
      <div class="col-md-6">
        <h3 class="fw-bold mb-1">{{ Str::title($u->nama) }}</h3>
        <div class="text-muted mb-2">{{ $u->username }}</div>
        <div class="d-flex flex-column gap-1">
          <span class="text-body-secondary">
            <i class="bi bi-briefcase me-1"></i>{{ Str::title($u->jabatan) }}
          </span>
          <span class="text-body-secondary">
            <i class="bi bi-building me-1"></i>{{ $u->bidang }}
          </span>
        </div>
      </div>

      {{-- Aksi (kanan: change atas, delete bawah) --}}
      <div class="col-md-3">
        <div class="d-grid gap-2">
          {{-- Change picture --}}
          <form id="formChangePhoto" method="POST" action="{{ route('account.photo') }}" enctype="multipart/form-data">
            @csrf
            <input id="inputPhoto" type="file" name="foto" accept="image/*" class="d-none">
            <button type="button" id="btnChange" class="btn btn-success btn-profile">Change picture</button>
          </form>

          {{-- Delete picture --}}
          <form method="POST" action="{{ route('account.photo.delete') }}">
            @csrf @method('DELETE')
            <button class="btn btn-danger btn-profile" {{ $u->foto ? '' : 'disabled' }}>Delete picture</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- Seksi informasi akun (read-only) --}}
  <div class="app-card p-4 p-md-5">
    <h5 class="fw-bold mb-3">Informasi Akun</h5>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Nama</label>
        <input class="form-control" value="{{ $u->nama }}" disabled>
      </div>
      <div class="col-md-6">
        <label class="form-label">Username</label>
        <input class="form-control" value="{{ $u->username }}" disabled>
      </div>
      <div class="col-md-6">
        <label class="form-label">Jabatan</label>
        <input class="form-control" value="{{ $u->jabatan }}" disabled>
      </div>
      <div class="col-md-6">
        <label class="form-label">Bidang</label>
        <input class="form-control" value="{{ $u->bidang }}" disabled>
      </div>
    </div>
  </div>

  {{-- Logout di PALING BAWAH --}}
  <div class="text-center my-4">
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button class="btn btn-outline-danger px-4">
        <i class="bi bi-box-arrow-right me-1"></i> Logout
      </button>
    </form>
  </div>
</div>

{{-- Bottom nav tetap --}}
<nav class="bottom-nav mt-4">
  <div class="container">
    <ul class="nav justify-content-around py-2">
      <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}"><i class="bi bi-house-door me-1"></i> Home</a></li>
      <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-graph-up me-1"></i> Statistik</a></li>
      <li class="nav-item"><a class="nav-link active" href="{{ route('account') }}"><i class="bi bi-person me-1"></i> Account</a></li>
    </ul>
  </div>
</nav>

@push('head')
<style>
  .btn-profile {
    display: inline-block;
    width: 100%;            /* biar sama panjang */
    font-size: 0.95rem;     /* seragam */
    padding: 0.6rem 1rem;   /* seragam */
    border-radius: 8px;
    font-weight: 600;
    text-align: center;
  }
</style>
@endpush

@push('scripts')
<script>
  const btn      = document.querySelector('#formChangePhoto button');
  const input    = document.getElementById('inputPhoto');
  const form     = document.getElementById('formChangePhoto');

  if (btn && input && form) {
    btn.addEventListener('click', () => input.click());
    input.addEventListener('change', () => {
      if (input.files && input.files.length) form.submit();
    });
  }
</script>
@endpush
@endsection