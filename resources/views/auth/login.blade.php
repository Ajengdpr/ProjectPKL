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
    background: url('{{ asset('img/bg.jpg') }}') center/cover no-repeat;
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

      <form method="POST" action="/login" class="mt-3">
        @csrf
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input class="form-control form-control-lg" name="username" value="{{ old('username') }}" required autofocus>
        </div>
        <div class="mb-2">
          <label class="form-label">Password</label>
          <div class="input-group">
            <input class="form-control form-control-lg" type="password" name="password" id="password" required>
            <span class="input-group-text" id="togglePassword">
              <i class="bi bi-eye-slash"></i>
            </span>
          </div>
        </div>
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" id="remember" name="remember">
          <label class="form-check-label" for="remember">Ingat saya</label>
        </div>
        <button class="btn btn-brand btn-lg w-100" type="submit">
          <i class="bi bi-box-arrow-in-right me-1"></i> Log In
        </button>
      </form>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
  const togglePassword = document.querySelector('#togglePassword');
  const password = document.querySelector('#password');

  togglePassword.addEventListener('click', function (e) {
    // toggle the type attribute
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);
    // toggle the eye slash icon
    this.querySelector('i').classList.toggle('bi-eye-slash');
    this.querySelector('i').classList.toggle('bi-eye');
  });
</script>
@endpush