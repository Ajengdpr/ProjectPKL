@extends('layouts.app')
@section('title', 'Login - E-Absensi DLH Kalsel')

@push('head')
<style>
    :root {
        --primary-blue: #2563eb;
        --primary-gradient: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
    }

    body {
        background-color: #f1f5f9;
    }

    .login-wrapper {
        min-height: 100vh;
        display: flex;
        background: white;
    }

    /* Left Side: Visual */
    .login-left {
        flex: 1.2;
        background: url('{{ asset('img/bg.jpg') }}') center/cover no-repeat;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: flex-start; /* Geser ke kiri */
        padding: 5rem; /* Tambah padding agar tidak terlalu mepet tembok */
        color: white;
    }

    .login-left::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.85) 0%, rgba(30, 64, 175, 0.9) 100%);
        z-index: 1;
    }

    .login-left-content {
        position: relative;
        z-index: 2;
        max-width: 550px;
    }

    .login-left-content h1 {
        font-weight: 800;
        font-size: 3.25rem;
        line-height: 1.1;
        margin-bottom: 1.5rem;
        letter-spacing: -1px;
    }

    .login-left-content p {
        font-size: 1.1rem;
        opacity: 0.9;
        font-weight: 500;
        line-height: 1.6;
        margin-bottom: 2rem;
    }

    /* Right Side: Form - Balanced */
    .login-right {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2.5rem;
        background-color: #f8fafc;
    }

    .login-card-modern {
        width: 100%;
        max-width: 450px; /* Diperbesar sedikit */
        background: white;
        padding: 3rem 3.5rem; /* Padding disesuaikan agar lebih lega */
        border-radius: 32px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.05);
        border: 1px solid #f1f5f9;
    }

    .logo-container {
        text-align: center;
        margin-bottom: 2rem;
    }

    .logo-container img {
        width: 75px;
        margin-bottom: 1rem;
        filter: drop-shadow(0 5px 15px rgba(0,0,0,0.1));
    }

    .logo-container h4 {
        font-weight: 800;
        color: #1e293b;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }

    .logo-container p {
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 500;
    }

    /* Modern Balanced Inputs */
    .form-label {
        font-weight: 700;
        color: #475569;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }

    .input-group-modern {
        position: relative;
        margin-bottom: 1.25rem;
    }

    .input-group-modern i {
        position: absolute;
        left: 1.25rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 1.1rem;
        z-index: 10;
    }

    .form-control-modern {
        width: 100%;
        padding: 0.8rem 1.25rem 0.8rem 3.25rem;
        border-radius: 16px;
        border: 2px solid #f1f5f9;
        background: #f8fafc;
        font-weight: 600;
        color: #1e293b;
        transition: all 0.2s;
    }

    .form-control-modern:focus {
        background: white;
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        outline: none;
    }

    .password-toggle {
        position: absolute;
        right: 1.25rem;
        left: auto !important;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #94a3b8;
        z-index: 11;
        font-size: 1.1rem;
        transition: all 0.2s;
        opacity: 0;
        visibility: hidden;
    }

    .input-group-modern:focus-within .password-toggle {
        opacity: 1;
        visibility: visible;
    }

    .password-toggle:hover {
        color: var(--primary-blue);
    }

    .form-control-password {
        padding-right: 3.5rem !important;
    }

    .btn-login-modern {
        background: var(--primary-gradient);
        color: white;
        width: 100%;
        padding: 0.9rem;
        border-radius: 16px;
        border: none;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 1rem;
        transition: all 0.3s;
        box-shadow: 0 10px 25px rgba(37, 99, 235, 0.2);
    }

    .btn-login-modern:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(37, 99, 235, 0.3);
        color: white;
    }

    .about-link {
        display: inline-block;
        color: white;
        text-decoration: none;
        font-weight: 700;
        border: 2px solid rgba(255,255,255,0.3);
        padding: 0.6rem 2rem;
        border-radius: 50px;
        transition: all 0.2s;
    }

    .about-link:hover {
        background: white;
        color: var(--primary-blue);
        border-color: white;
    }

    /* Mobile Responsive */
    @media (max-width: 991px) {
        .login-left { display: none; }
        .login-right { background: var(--primary-gradient); min-height: 100vh; }
        .login-card-modern { padding: 2rem; }
    }

    /* Shorter Laptop Screen Optimization (Fix for hidden login button) */
    @media (max-height: 850px) {
        .login-card-modern {
            padding: 1.75rem 2.5rem;
        }
        .logo-container {
            margin-bottom: 1.25rem;
        }
        .logo-container img {
            width: 60px;
        }
        .logo-container h4 {
            font-size: 1.25rem;
            margin-bottom: 0.25rem;
        }
        .login-left-content h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        .login-left-content p {
            margin-bottom: 1.5rem;
            font-size: 1rem;
        }
        .input-group-modern {
            margin-bottom: 1rem;
        }
        .form-label {
            margin-bottom: 0.35rem;
        }
        .form-control-modern {
            padding: 0.65rem 1.25rem 0.65rem 3rem;
        }
        .mb-4 {
            margin-bottom: 1rem !important;
        }
    }
</style>
@endpush

@section('content')
<div class="login-wrapper">
    <div class="login-left">
        <div class="login-left-content">
            <h1>E-Absensi<br>Pegawai</h1>
            <p>Sistem Informasi Manajemen Absensi Terintegrasi<br>Dinas Lingkungan Hidup Provinsi Kalimantan Selatan</p>
            <a href="#tentang" class="about-link">Pelajari Selengkapnya</a>
        </div>
    </div>

    <div class="login-right">
        <div class="login-card-modern">
            <div class="logo-container">
                <img src="{{ asset('img/Logo_Provinsi.png') }}" alt="Logo">
                <h4>Selamat Datang</h4>
                <p>Silakan masuk ke akun Anda</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger border-0 rounded-4 small mb-4 py-3 shadow-sm">
                    <i class="bi bi-exclamation-circle-fill me-2"></i> {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="/login">
                @csrf
                <div class="mb-4">
                    <label class="form-label">Nama Pengguna</label>
                    <div class="input-group-modern">
                        <i class="bi bi-person-fill"></i>
                        <input class="form-control-modern" name="username" value="{{ old('username') }}" placeholder="Username" required autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Kata Sandi</label>
                    <div class="input-group-modern">
                        <i class="bi bi-shield-lock-fill"></i>
                        <input class="form-control-modern form-control-password" type="password" name="password" id="password" placeholder="Password" required>
                        <i class="bi bi-eye-slash password-toggle" id="togglePassword"></i>
                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const togglePassword = document.querySelector('#togglePassword');
                        const password = document.querySelector('#password');

                        if (togglePassword && password) {
                            togglePassword.addEventListener('click', function() {
                                // toggle the type attribute
                                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                                password.setAttribute('type', type);
                                
                                // toggle the eye / eye slash icon
                                this.classList.toggle('bi-eye');
                                this.classList.toggle('bi-eye-slash');
                            });
                        }
                    });
                </script>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember" style="cursor:pointer;">
                        <label class="form-check-label text-muted small fw-bold" for="remember" style="cursor:pointer;">Ingat saya</label>
                    </div>
                </div>

                <button class="btn btn-login-modern" type="submit">
                    Login <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Section Tentang --}}
<section id="tentang" class="py-5" style="background-color: #f8fafc; border-top: 1px solid #e2e8f0;">
    <div class="container py-4">
        <div class="text-center mb-5" style="max-width: 900px; margin: 0 auto;">
            <h2 class="fw-bold text-dark mb-3">E-Absensi DLH Kalsel</h2>
            <p class="text-secondary fs-6 leading-relaxed">
                E-Absensi Pegawai Dinas Lingkungan Hidup Provinsi Kalimantan Selatan merupakan sistem terintegrasi yang digunakan untuk mendukung pengelolaan absensi pegawai. Sistem ini dirancang agar lebih transparan, akuntabel, efisien, dan terpadu.
            </p>
            <div class="mx-auto bg-primary rounded-pill mt-4" style="width: 50px; height: 4px; opacity: 0.3;"></div>
        </div>

        <div class="row g-4 justify-content-center">
            @php
                $features = [
                    ['icon' => 'bi-eye', 'title' => 'Transparan', 'color' => 'warning', 'desc' => 'Riwayat absensi dapat dipantau langsung kapan saja.'],
                    ['icon' => 'bi-shield-check', 'title' => 'Akuntabel', 'color' => 'primary', 'desc' => 'Data absensi tercatat secara otomatis dan transparan.'],
                    ['icon' => 'bi-lightning-charge', 'title' => 'Efisien', 'color' => 'success', 'desc' => 'Rekapitulasi data absensi bulanan jadi lebih cepat.'],
                    ['icon' => 'bi-diagram-3', 'title' => 'Terpadu', 'color' => 'info', 'desc' => 'Sistem terhubung antara data pegawai dan lokasi.'],
                ];
            @endphp
            @foreach($features as $f)
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 p-4 transition-hover h-100 text-center" style="background: white;">
                    <div class="d-inline-flex align-items-center justify-content-center bg-{{$f['color']}} bg-opacity-10 text-{{$f['color']}} rounded-4 mb-3" style="width: 56px; height: 56px; font-size: 1.5rem; margin: 0 auto;">
                        <i class="bi {{$f['icon']}}"></i>
                    </div>
                    <h6 class="fw-bold mb-2">{{$f['title']}}</h6>
                    <p class="text-muted small mb-0">{{$f['desc']}}</p>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-5 p-4 rounded-4 text-white text-center shadow-lg" style="background: var(--primary-gradient) !important;">
            <p class="mb-0 fs-6 fw-bold">Dinas Lingkungan Hidup Provinsi Kalimantan Selatan</p>
            <div class="small opacity-75 mt-1">Jl. Bangun Praja, Kawasan Perkantoran Pemerintah Provinsi Kalimantan Selatan, Palam, Kec. Cempaka, Kota Banjar Baru, Kalimantan Selatan 70732.</div>
        </div>
    </div>
</section>

<style>
    .transition-hover { transition: all 0.3s ease; }
    .transition-hover:hover { transform: translateY(-10px); box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important; }
</style>
@endsection
