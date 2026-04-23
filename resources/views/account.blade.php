@extends('layouts.app')
@section('title','Profil Saya')

@section('content')
@php
  $u   = $user ?? auth()->user();
  $src = $u->foto ? asset('storage/'.$u->foto) : asset('img/default-avatar.jpg');
@endphp

<div class="container-fluid py-4 px-md-5">
    {{-- Notifikasi --}}
    @if(session('ok'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('ok') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Header Banner Profil (Full Width) --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="profile-banner p-4 p-md-5 text-white" style="background: linear-gradient(135deg, #0d6efd 0%, #003d99 100%);">
            <div class="row align-items-center g-4">
                <div class="col-md-auto text-center text-md-start">
                    <div class="position-relative d-inline-block">
                        <img src="{{ $src }}" alt="Foto {{Str::title($u->nama)}}"
                             class="rounded-circle shadow-lg border border-4 border-white border-opacity-25"
                             style="width: 150px; height: 140px; object-fit: cover;">
                        
                        <form id="formChangePhoto" method="POST" action="{{ route('account.photo') }}" enctype="multipart/form-data">
                            @csrf
                            <input type="file" id="inputPhoto" name="foto" class="d-none" accept="image/*">
                            <button type="button" class="btn btn-light btn-sm rounded-circle position-absolute shadow" 
                                    style="bottom: 5px; right: 5px; width: 38px; height: 38px; padding: 0;"
                                    id="btnChange" title="Ganti Foto">
                                <i class="bi bi-camera-fill text-primary"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="col-md text-center text-md-start">
                    <h2 class="fw-bold mb-1">{{ Str::title($u->nama) }}</h2>
                    <p class="mb-3 opacity-75 fs-5">@<span>{{ $u->username }}</span> | {{ Str::title($u->jabatan) }}</p>
                    <div class="d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                        <span class="badge bg-white bg-opacity-25 text-white rounded-pill px-3 py-2 border border-white border-opacity-25">
                            <i class="bi bi-building me-1"></i> {{ $u->bidang }}
                        </span>
                    </div>
                </div>
                <div class="col-md-auto text-center text-md-end mt-4 mt-md-0">
                    <div class="d-flex flex-md-column gap-2 justify-content-center">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" 
                                data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                            <i class="bi bi-shield-lock-fill me-2 text-primary"></i> Keamanan Akun
                        </button>
                        <button type="button" class="btn btn-outline-light rounded-pill px-4" 
                                data-bs-toggle="modal" data-bs-target="#confirmDeletePhotoModal"
                                {{ $u->foto ? '' : 'disabled' }}>
                            <i class="bi bi-trash-fill me-2"></i> Hapus Foto
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Grid Detail Informasi (Padat & Memenuhi Ruang) --}}
    <div class="row g-4">
        {{-- Card: Data Pribadi (Sisi Kiri) --}}
        <div class="col-xl-8 col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h5 class="fw-bold mb-0 d-flex align-items-center">
                        <i class="bi bi-card-list text-primary me-2"></i> Rincian Profil Pegawai
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="row g-0">
                        <div class="col-md-6 border-end border-bottom p-4 info-item">
                            <label class="text-muted small fw-bold text-uppercase mb-1 d-block">Nama Lengkap</label>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-person-check-fill text-primary me-2 fs-5"></i>
                                <span class="fs-6 fw-bold text-dark">{{ $u->nama }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 border-bottom p-4 info-item">
                            <label class="text-muted small fw-bold text-uppercase mb-1 d-block">Username</label>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-at text-primary me-2 fs-5"></i>
                                <span class="fs-6 fw-bold text-dark">{{ $u->username }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 border-end p-4 info-item">
                            <label class="text-muted small fw-bold text-uppercase mb-1 d-block">Jabatan Sekarang</label>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-briefcase-fill text-primary me-2 fs-5"></i>
                                <span class="fs-6 fw-bold text-dark">{{ Str::title($u->jabatan) }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 p-4 info-item">
                            <label class="text-muted small fw-bold text-uppercase mb-1 d-block">Bidang Kerja / Bagian</label>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-building-fill text-primary me-2 fs-5"></i>
                                <span class="fs-6 fw-bold text-dark">{{ $u->bidang }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sisi Kanan: Tips Keamanan --}}
        <div class="col-xl-4 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-primary bg-opacity-10 border border-primary border-opacity-25">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3 text-primary">
                        <i class="bi bi-shield-lock-fill fs-4 me-2"></i>
                        <h6 class="fw-bold mb-0 text-uppercase small" style="letter-spacing: 1px;">Tips Keamanan Akun</h6>
                    </div>
                    <p class="text-muted small mb-0 lh-base text-justify" style="text-align: justify;">
                        Jaga kerahasiaan akun Anda dengan tidak memberitahukan password login kepada siapapun dan pastikan untuk selalu logout setelah selesai menggunakan perangkat publik.
                    </p>
                </div>
            </div>
            
            <div class="p-4 bg-white shadow-sm rounded-4 border-start border-4 border-warning">
                <h6 class="fw-bold small mb-1">Butuh Bantuan?</h6>
                <p class="text-muted small mb-0">Hubungi Admin jika terdapat ketidaksesuian pada data profil Anda.</p>
            </div>
        </div>
    </div>
</div>

{{-- Modal Konfirmasi Hapus Foto --}}
<div class="modal fade" id="confirmDeletePhotoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4 text-center p-4">
            <i class="bi bi-exclamation-triangle text-danger display-4 mb-3"></i>
            <h5 class="fw-bold">Hapus Foto?</h5>
            <p class="text-muted small mb-4">Foto akan dikembalikan ke avatar bawaan sistem.</p>
            <div class="d-grid gap-2">
                <form method="POST" action="{{ route('account.photo.delete') }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100 rounded-pill mb-2">Ya, Hapus</button>
                </form>
                <button type="button" class="btn btn-light w-100 rounded-pill" data-bs-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Ganti Password --}}
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form method="POST" action="{{ route('account.password.update') }}">
                @csrf
                <div class="modal-header bg-primary text-white border-0 py-3">
                    <h5 class="modal-title fw-bold">Ubah Password</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Password Lama</label>
                        <input type="password" class="form-control bg-light border-0 py-2" name="password_lama" required>
                    </div>
                    <hr class="my-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Password Baru</label>
                        <input type="password" class="form-control bg-light border-0 py-2" name="password_baru" placeholder="Min. 8 Karakter" required minlength="8">
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control bg-light border-0 py-2" name="password_baru_confirmation" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .info-item { transition: 0.2s; }
    .info-item:hover { background-color: #f8faff; }
    .profile-banner { min-height: 250px; display: flex; align-items: center; }
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