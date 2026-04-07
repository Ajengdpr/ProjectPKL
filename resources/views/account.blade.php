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

          {{-- Tombol Ganti Password (Modal Trigger) --}}
          <button type="button" class="btn btn-warning btn-profile" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
            <i class="bi bi-key-fill me-1"></i> Change Password
          </button>
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

</div>

{{-- Modal untuk Ganti Password --}}
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ route('account.password.update') }}">
          @csrf
          <div class="modal-header">
            <h5 class="modal-title" id="changePasswordModalLabel">Ganti Password</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Password Lama</label>
                    <input type="password" class="form-control" name="password_lama" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Password Baru</label>
                    <input type="password" class="form-control" name="password_baru" required minlength="8">
                </div>
                <div class="col-12">
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" class="form-control" name="password_baru_confirmation" required>
                </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Update Password</button>
          </div>
      </form>
    </div>
  </div>
</div>

@push('head')
<style>
  .btn-profile {
    display: inline-block;
    width: 100%;           
    font-size: 0.95rem;     
    padding: 0.6rem 1rem;   
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