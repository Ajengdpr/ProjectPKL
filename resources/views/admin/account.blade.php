@extends('layouts.admin')
@section('title','Profil Admin')

@section('content')
@php
  $u   = $user ?? auth()->user();
  $src = $u->foto ? asset('storage/'.$u->foto).'?v='.time() : asset('img/default-avatar.jpg');
@endphp

<div class="container-fluid py-4 px-md-5">
    {{-- Notifikasi --}}
    @if(session('ok'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('ok') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Header Banner Profil Admin --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="profile-banner p-3 p-md-4 text-white" style="background: linear-gradient(135deg, #0d6efd 0%, #003d99 100%); min-height: auto;">
            <div class="row align-items-center g-3">
                <div class="col-md-auto text-center text-md-start">
                    <div class="position-relative d-inline-block">
                        <img src="{{ $src }}" alt="Foto {{Str::title($u->nama)}}"
                             class="rounded-circle shadow-lg border border-4 border-white border-opacity-25"
                             style="width: 100px; height: 100px; object-fit: cover;">
                        
                        <form id="formChangePhoto" method="POST" action="{{ route('account.photo') }}" enctype="multipart/form-data">
                            @csrf
                            <input type="file" id="inputPhoto" name="foto" class="d-none" accept=".jpg,.jpeg,.png,.webp">
                            <button type="button" class="btn btn-light btn-sm rounded-circle position-absolute shadow" 
                                    style="bottom: 0; right: 0; width: 32px; height: 32px; padding: 0;"
                                    id="btnChange" title="Ganti Foto">
                                <i class="bi bi-camera-fill text-primary" style="font-size: 0.85rem;"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="col-md text-center text-md-start">
                    <h3 class="fw-bold mb-1">{{ Str::title($u->nama) }} <span class="badge bg-white bg-opacity-25 fs-6 align-middle ms-1" style="font-size: 0.75rem !important;">ADMIN</span></h3>
                    <p class="mb-2 opacity-75 small">@<span>{{ $u->username }}</span> | {{ Str::title($u->jabatan) }}</p>
                    <div class="d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                        <span class="badge bg-white bg-opacity-25 text-white rounded-pill px-3 py-1 border border-white border-opacity-25 small">
                            <i class="bi bi-building me-1"></i> {{ $u->bidang }}
                        </span>
                    </div>
                </div>
                <div class="col-md-auto text-center text-md-end">
                    <div class="d-flex flex-md-column gap-2 justify-content-center">
                        <button type="button" class="btn btn-light btn-sm rounded-pill px-4 fw-bold" 
                                data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                            <i class="bi bi-shield-lock-fill me-2 text-primary"></i> Ubah Kata Sandi
                        </button>
                        <button type="button" class="btn btn-outline-light btn-sm rounded-pill px-4" 
                                data-bs-toggle="modal" data-bs-target="#confirmDeletePhotoModal"
                                {{ $u->foto ? '' : 'disabled' }}>
                            <i class="bi bi-trash-fill me-2"></i> Hapus Foto
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Grid Detail Informasi --}}
    <div class="row g-4">
        <div class="col-xl-8 col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h6 class="fw-bold mb-0 d-flex align-items-center">
                        <i class="bi bi-card-list text-primary me-2"></i> Rincian Profil Admin
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="row g-0">
                        <div class="col-md-6 border-end border-bottom p-3 info-item">
                            <label class="text-muted small fw-bold text-uppercase mb-1 d-block" style="font-size: 0.65rem;">Nama Lengkap</label>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-person-check-fill text-primary me-2"></i>
                                <span class="fw-bold text-dark small">{{ $u->nama }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 border-bottom p-3 info-item">
                            <label class="text-muted small fw-bold text-uppercase mb-1 d-block" style="font-size: 0.65rem;">Username</label>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-at text-primary me-2"></i>
                                <span class="fw-bold text-dark small">{{ $u->username }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 border-end p-3 info-item">
                            <label class="text-muted small fw-bold text-uppercase mb-1 d-block" style="font-size: 0.65rem;">Jabatan</label>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-briefcase-fill text-primary me-2"></i>
                                <span class="fw-bold text-dark small">{{ Str::title($u->jabatan) }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 p-3 info-item">
                            <label class="text-muted small fw-bold text-uppercase mb-1 d-block" style="font-size: 0.65rem;">Bidang Kerja</label>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-building-fill text-primary me-2"></i>
                                <span class="fw-bold text-dark small">{{ $u->bidang }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-3 bg-primary bg-opacity-10 border border-primary border-opacity-25">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2 text-primary">
                        <i class="bi bi-shield-lock-fill fs-5 me-2"></i>
                        <h6 class="fw-bold mb-0 text-uppercase small" style="font-size: 0.7rem; letter-spacing: 1px;">Keamanan Akun</h6>
                    </div>
                    <p class="text-muted small mb-0 lh-sm">
                        Sebagai Admin, pastikan untuk selalu menjaga kerahasiaan kredensial Anda dan melakukan logout setelah selesai bertugas.
                    </p>
                </div>
            </div>
            
            <div class="p-3 bg-white shadow-sm rounded-4 border-start border-4 border-warning">
                <h6 class="fw-bold small mb-1">Butuh Bantuan?</h6>
                <p class="text-muted small mb-0">Hubungi Pengembang jika terdapat masalah teknis pada akses akun Admin Anda.</p>
            </div>
        </div>
    </div>
</div>

{{-- Modals --}}
<div class="modal fade" id="confirmDeletePhotoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4 text-center p-4">
            <i class="bi bi-exclamation-triangle text-danger display-4 mb-3"></i>
            <h5 class="fw-bold">Hapus Foto?</h5>
            <p class="text-muted small mb-4">Foto akan dikembalikan ke avatar bawaan.</p>
            <div class="d-grid gap-2">
                <form method="POST" action="{{ route('account.photo.delete') }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger rounded-pill">Ya, Hapus</button>
                </form>
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form method="POST" action="{{ route('account.password.update') }}">
                @csrf
                <div class="modal-header bg-primary text-white border-0 py-3">
                    <h5 class="modal-title fw-bold small">Ubah Kata Sandi Akun</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Kata Sandi Lama</label>
                        <input type="password" class="form-control form-control-sm bg-light border-0 py-2" name="password_lama" required>
                    </div>
                    <hr class="my-3">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Kata Sandi Baru</label>
                        <input type="password" class="form-control form-control-sm bg-light border-0 py-2" name="password_baru" placeholder="Min. 8 Karakter" required minlength="8">
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold">Konfirmasi Kata Sandi</label>
                        <input type="password" class="form-control form-control-sm bg-light border-0 py-2" name="password_baru_confirmation" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light btn-sm rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('head')
<style>
    .info-item { transition: 0.2s; }
    .info-item:hover { background-color: #f8faff; }
</style>
@endpush

@push('scripts')
<script>
    const btnChange = document.getElementById('btnChange');
    const inputPhoto = document.getElementById('inputPhoto');
    const formPhoto = document.getElementById('formChangePhoto');
    if (btnChange && inputPhoto && formPhoto) {
        btnChange.addEventListener('click', () => inputPhoto.click());
        inputPhoto.addEventListener('change', () => {
            if (inputPhoto.files && inputPhoto.files.length) {
                btnChange.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
                formPhoto.submit();
            }
        });
    }
</script>
@endpush
@endsection
