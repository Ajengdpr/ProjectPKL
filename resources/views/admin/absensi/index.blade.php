@extends('layouts.admin')
@section('title','Manajemen Absensi')

@section('content')
<div class="container" style="max-width:1100px">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h4 fw-bold">Manajemen Absensi</h1>
    <a href="{{ route('admin.absensi.export.csv', request()->only('from','to','user_id','bidang','status')) }}"
       class="btn btn-outline-secondary"><i class="bi bi-download me-1"></i>Export CSV</a>
  </div>

  {{-- Filter --}}
  <form method="get" class="row g-2 mb-3">
    <div class="col-6 col-md-2">
      <input type="date" name="from" value="{{ request('from') }}" class="form-control" placeholder="Dari">
    </div>
    <div class="col-6 col-md-2">
      <input type="date" name="to" value="{{ request('to') }}" class="form-control" placeholder="Sampai">
    </div>
    <div class="col-12 col-md-3">
      <select name="user_id" class="form-select">
        <option value="">-- Semua Pegawai --</option>
        @foreach($users as $u)
          <option value="{{ $u->id }}" @selected(request('user_id')==$u->id)>{{ $u->nama }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-6 col-md-2">
      <select name="bidang" class="form-select">
        <option value="">-- Semua Bidang --</option>
        @foreach($bidangs as $b)
          <option value="{{ $b }}" @selected(request('bidang')==$b)>{{ $b }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-6 col-md-2">
      <select name="status" class="form-select">
        <option value="">-- Semua Status --</option>
        @foreach(['hadir','terlambat','izin','sakit','alpha'] as $s)
          <option value="{{ $s }}" @selected(request('status')==$s)>{{ ucfirst($s) }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-12 col-md-1">
      <button class="btn btn-primary w-100">Cari</button>
    </div>
  </form>

  {{-- Tabel --}}
  <div class="app-card p-0">
    <div class="table-responsive">
      <table class="table table-sm align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width:130px;">Tanggal</th>
            <th>Nama</th>
            <th style="width:120px;">Status</th>
            <th>Alasan</th>
            <th style="width:130px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @php
            $badge = fn($s) => match($s){
              'hadir'=>'bg-success-subtle text-success',
              'terlambat'=>'bg-warning-subtle text-warning',
              'izin'=>'bg-primary-subtle text-primary',
              'sakit'=>'bg-info-subtle text-info',
              'alpha'=>'bg-danger-subtle text-danger',
              default=>'bg-secondary-subtle text-secondary'
            };
          @endphp
          @forelse($absensi as $a)
            <tr>
              <td>{{ $a->tanggal }}</td>
              <td class="fw-medium">{{ $a->user->nama ?? '-' }}</td>
              <td><span class="badge rounded-pill {{ $badge($a->status) }}">{{ strtoupper($a->status) }}</span></td>
              <td>{{ $a->alasan }}</td>
              <td>
                <a href="{{ route('admin.absensi.edit',$a) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                <form action="{{ route('admin.absensi.destroy',$a) }}" method="post" class="d-inline"
                      onsubmit="return confirm('Hapus data ini?')">
                  @csrf @method('delete')
                  <button class="btn btn-sm btn-outline-danger">Hapus</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-body-secondary py-3">Tidak ada data.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="p-2">
      {{ $absensi->links() }}
    </div>
  </div>

  {{-- Input Manual --}}
  <h2 class="h5 fw-bold mt-4 mb-2">Input Absensi Manual</h2>
  <form method="post" action="{{ route('admin.absensi.store') }}" class="row g-2">
    @csrf
    <div class="col-12 col-md-4">
      <select name="user_id" class="form-select" required>
        <option value="">-- Pilih Pegawai --</option>
        @foreach($users as $u)
          <option value="{{ $u->id }}">{{ $u->nama }} ({{ $u->username }})</option>
        @endforeach
      </select>
    </div>
    <div class="col-6 col-md-2">
      <input type="date" name="tanggal" class="form-control" required>
    </div>
    <div class="col-6 col-md-2">
      <select name="status" class="form-select" required>
        <option value="hadir">Hadir</option>
        <option value="terlambat">Terlambat</option>
        <option value="izin">Izin</option>
        <option value="sakit">Sakit</option>
        <option value="alpha">Alpha</option>
      </select>
    </div>
    <div class="col-12 col-md-3">
      <input name="alasan" class="form-control" placeholder="Alasan (opsional)">
    </div>
    <div class="col-12 col-md-1">
      <button class="btn btn-primary w-100">Simpan</button>
    </div>
  </form>
</div>
@endsection