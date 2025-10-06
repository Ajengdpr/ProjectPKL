@extends('layouts.admin')
@section('title','Pengaturan')

@section('content')
<div class="container has-bottom-nav" style="max-width:1100px">
  <h1 class="h4 fw-bold mb-3">Pengaturan Aplikasi</h1>
    @if(session('ok'))
      <div class="alert alert-success">{{ session('ok') }}</div>
    @endif
    @if($errors->any())
      <div class="alert alert-danger mb-3">
        <ul class="mb-0">
          @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    <form method="post" action="{{ route('admin.settings.save') }}" class="app-card p-3">
      @csrf

      <h6 class="fw-bold mb-2">Poin</h6>
      <div class="row g-2 mb-3">
        @foreach(\App\Models\Absensi::getStatuses() as $key=>$label)
          <div class="col-6 col-md-2">
            <label class="form-label small text-body-secondary">{{ $label }}</label>
            <input type="number" class="form-control" name="poin[{{ $key }}]" value="{{ $poin[$key] ?? 0 }}">
          </div>
        @endforeach
      </div>

      <h6 class="fw-bold mb-2">Lokasi</h6>
      <div class="row g-2 mb-3">
        <div class="col-12 col-md-4">
          <label class="form-label small text-body-secondary">Latitude</label>
          <input type="number" step="any" class="form-control" name="lokasi[lat]" value="{{ $lokasi['lat'] }}">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label small text-body-secondary">Longitude</label>
          <input type="number" step="any" class="form-control" name="lokasi[lng]" value="{{ $lokasi['lng'] }}">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label small text-body-secondary">Radius (meter)</label>
          <input type="number" class="form-control" name="lokasi[radius]" value="{{ $lokasi['radius'] }}">
        </div>
      </div>

      <h6 class="fw-bold mb-2">Jam</h6>
      <div class="row g-2 mb-4">
        <div class="col-12 col-md-4">
          <label class="form-label small text-body-secondary">Batas Hadir</label>
          <input type="time" step="1" class="form-control" name="jam[batas_hadir]" value="{{ $jam['batas_hadir'] }}">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label small text-body-secondary">Batas Akhir</label>
          <input type="time" step="1" class="form-control" name="jam[batas_akhir]" value="{{ $jam['batas_akhir'] ?? '16:00:00' }}">
        </div>
      </div>

      <div class="d-flex gap-2">
        <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan</button>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Kembali ke Dashboard</a>
      </div>
    </form>

  <div class="my-3"></div>
</div>
@endsection
