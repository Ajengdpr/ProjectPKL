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
            <label class="form-label">
              Alasan {{ $required ? '(wajib)' : '(opsional)' }}
            </label>
            <textarea name="alasan" class="form-control" {{ $required ? 'required' : '' }} maxlength="500"
                      placeholder="{{ $preset==='Terlambat' ? 'Cth: ban bocor, macet, ada keperluan mendadak...' : 'Isi jika perlu' }}"></textarea>
            @if($preset==='Terlambat')
              <div class="form-text">
                Poin dikurangi <b>-3</b> jika ada alasan, <b>-5</b> jika kosong.
              </div>
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