@extends('layouts.app')
@section('title','Login')

@push('head')
<style>
  .login-hero{
    min-height: 100vh;           /* full tinggi layar */
    margin: 0;                   /* jangan ada ruang */
    display:flex;align-items:center;justify-content:center;
    background: #0f172a url('{{ asset('img/bg.jpg') }}') center/cover no-repeat fixed;
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
          <input class="form-control form-control-lg" type="password" name="password" required>
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