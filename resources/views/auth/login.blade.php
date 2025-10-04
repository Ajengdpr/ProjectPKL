@extends('layouts.app')
@section('title','Login')

@push('head')
<style>
  .login-hero{
    min-height: 100vh;           
    margin: 0;                   
    display:flex;align-items:center;justify-content:center;
    background: #0f172a url('{{ asset('img/depan.jpeg') }}') center/cover no-repeat fixed;
  }
  .login-card{ width: min(440px, 92vw); }
</style>
@endpush


@section('content')
<section class="login-hero">
  <div class="container d-flex justify-content-center">
    <div class="app-card p-4 p-md-5 login-card">
      <div class="text-center mb-3">
        <h4 class="fw-bold mb-1">Absensi Pegawai</h4>
        <div class="text-secondary">Masuk dengan akun Anda</div>
      </div>

      @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
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