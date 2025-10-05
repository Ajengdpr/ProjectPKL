@extends('layouts.app')
@section('title','Login')

@push('head')
<style>
  .login-wrapper {
    min-height: 100vh;
    display: flex;
  }
  .login-left {
    flex: 1;
    background: url('{{ asset('img/depan.jpeg') }}') center/cover no-repeat;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    padding: 2rem;
    position: relative;
  }
  .login-left::before {
    content: "";
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.55); /* overlay gelap biar teks keliatan */
  }
  .login-left-content {
    position: relative;
    z-index: 2;
    max-width: 500px;
    text-align: left;
  }
  .login-right {
    flex: 0 0 420px;
    background:#80a9f7; /* biru terang */
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
  }
  .login-card {
    width: 100%;
    max-width: 360px;
  }
</style>
@endpush

@section('content')
<div class="login-wrapper">
  <!-- Left side -->
  <div class="login-left">
    <div class="login-left-content">
      <h3 class="fw-bold display-8">
        Sistem Absensi Pegawai <br>
        Dinas Lingkungan Hidup Provinsi <br>
        Kalimantan Selatan
      </h3>
      <p class="fs-6 text-light fw-semibold">
        Masuk untuk mengakses sistem absensi pegawai terintegrasi
      </p>
      <!-- Tombol Tentang -->
      <a href="#tentang" class="btn btn-outline-light rounded-pill mt-3 px-4">
        Tentang
      </a>
    </div>
  </div>

  <!-- Right side -->
  <div class="login-right">
    <img src="{{ asset('img/Logo_Provinsi.png') }}" alt="Logo" class="mb-3" style="width:120px;">
    <h4 class="fw-bold mb-4">ABSENSI PEGAWAI</h4>
    @if($errors->any())
      <div class="alert alert-danger w-100">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="/login" class="login-card">
      @csrf
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input class="form-control form-control-lg" name="username" value="{{ old('username') }}" required autofocus>
      </div>
      <div class="mb-2">
        <label class="form-label">Password</label>
        <input class="form-control form-control-lg" type="password" name="password" required>
      </div>
      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" id="remember" name="remember">
        <label class="form-check-label" for="remember">Ingat saya</label>
      </div>
      <button class="btn btn-dark btn-lg w-100" type="submit">
        <i class="bi bi-box-arrow-in-right me-1"></i> Log In
      </button>
    </form>
  </div>
</div>

<!-- Section Tentang -->
<section id="tentang" class="py-1 mt-1">
  <div class="container">
    <div class="row g-4">
      <!-- Kolom Kiri -->
      <div class="col-md-5 position-relative rounded overflow-hidden">
        <!-- Background foto + overlay merah -->
        <div style="
          background: url('{{ asset('img/lingkungan.jpg') }}') center/cover no-repeat;
          width: 100%; height: 100%; position: absolute; inset: 0;">
        </div>
        <div style="
          background-color: rgba(243,93,93,0.7);
          width: 100%; height: 100%; position: absolute; inset: 0;">
        </div>

        <!-- Konten teks -->
        <div class="d-flex flex-column justify-content-center align-items-center p-5 text-center position-relative text-white" style="z-index:2; height:100%;">
          <i class="bi bi-info-circle fs-1 mb-3"></i>
          <h4 class="fw-bold">TENTANG</h4>
        </div>
      </div>

      <!-- Kolom Kanan -->
      <div class="col-md-7 p-5 rounded shadow-sm"
           style="background-color: rgba(255,255,255,0.85);">
        <h3 class="fw-bold" style="color:#f35d5d;">Sistem Absensi DLH Kalsel</h3>
        <p class="mt-3 text-secondary">
          <strong>Sistem Absensi Pegawai</strong> Dinas Lingkungan Hidup Provinsi Kalimantan Selatan
          merupakan sistem terintegrasi yang digunakan untuk mendukung pengelolaan kehadiran pegawai.
          Sistem ini dirancang agar lebih <em>transparan</em>, <em>efisien</em>, dan <em>terstruktur</em>.
        </p>
        <ul class="text-secondary">
          <li>Akuntabel</li>
          <li>Transparan</li>
          <li>Efisien</li>
          <li>Terdepan dalam pelayanan publik</li>
        </ul>
      </div>
    </div>
  </div>
</section>
@endsection