@extends('layouts.app')
@section('title', 'Input '.$preset)

@section('content')
<div class="container">
  <div class="row justify-content-center">
    <div class="col-xl-5 col-lg-6">
      <div class="app-card p-4">
        <h5 class="mb-3">Input Absensi: <strong>{{ $preset }}</strong></h5>

        @if($errors->any())
          <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('absen.store') }}">
          @csrf
          <input type="hidden" name="status" value="{{ $preset }}">

          <div class="mb-3">
             @if($preset === 'Hadir')
              <!-- Tidak ada kolom alasan untuk Hadir -->
              <label class="form-label">Alasan</label>
              <input type="text" class="form-control" disabled value="Tidak perlu alasan">
            @elseif($preset === 'Izin' || $preset === 'Terlambat')
              <label class="form-label" required>Alasan (Wajib)</label>
              <textarea name="alasan" class="form-control" required maxlength="500" placeholder="Isi alasan untuk {{ strtolower($preset) }}"></textarea>
              @if($preset === 'Terlambat')
                <div class="form-text">
                  Poin dikurangi <b>-3</b> jika ada alasan, <b>-5</b> jika kosong.
                </div>
              @endif
            @else
              <label class="form-label">Keterangan (Opsional)</label>
              <textarea name="alasan" class="form-control" maxlength="500" placeholder="Isi jika perlu"></textarea>
            @endif
          </div>

          <div class="d-flex gap-2">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Batal</a>
            <button class="btn btn-brand">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
