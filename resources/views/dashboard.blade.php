@extends('layouts.app')
@section('title','Dashboard')

@section('content')
<div class="container">

  {{-- Alerts --}}
  @if(session('ok'))
    <div class="alert alert-success">{{ session('ok') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
  @endif

  {{-- Hero --}}
  <div class="text-center my-3">
    <img src="{{ asset('img/halamandepan.jpeg') }}" alt="gedung" class="img-fluid" style="max-height:220px">
    <div class="mt-2">
  <span class="badge rounded-pill text-bg-primary px-3 py-2">
    POINT: <strong>{{ $user->point ?? 0 }}</strong>
  </span>
</div>
  </div>

  {{-- Tiles --}}
  <div class="row g-3">
    <div class="col-12 col-md-4">
      <a class="tile cyan w-100 text-decoration-none" data-bs-toggle="modal" data-bs-target="#absenModal" onclick="setStatus('Hadir')">
        <i class="bi bi-person"></i><h6>Hadir</h6>
      </a>
    </div>
    <div class="col-12 col-md-4">
      <a class="tile yellow w-100 text-decoration-none" data-bs-toggle="modal" data-bs-target="#absenModal" onclick="setStatus('Cuti')">
        <i class="bi bi-x-circle"></i><h6>Cuti</h6>
      </a>
    </div>
    <div class="col-12 col-md-4">
      <a class="tile green w-100 text-decoration-none" data-bs-toggle="modal" data-bs-target="#absenModal" onclick="setStatus('Tugas Luar')">
        <i class="bi bi-airplane"></i><h6>Tugas Luar</h6>
      </a>
    </div>

    <div class="col-12 col-md-4">
      <a class="tile gray w-100 text-decoration-none" data-bs-toggle="modal" data-bs-target="#absenModal" onclick="setStatus('Sakit')">
        <i class="bi bi-emoji-frown"></i><h6>Sakit</h6>
      </a>
    </div>
    <div class="col-12 col-md-4">
      <a class="tile red w-100 text-decoration-none" data-bs-toggle="modal" data-bs-target="#absenModal" onclick="setStatus('Terlambat')">
        <i class="bi bi-alarm"></i><h6>Terlambat</h6>
      </a>
    </div>
    <div class="col-12 col-md-4">
      <a class="tile dark w-100 text-decoration-none" data-bs-toggle="modal" data-bs-target="#absenModal" onclick="setStatus('Izin')">
        <i class="bi bi-phone"></i><h6>Izin Tidak Masuk</h6>
      </a>
    </div>
  </div>

  {{-- Keterangan Poin --}}
  <div class="row mt-4 g-3">
    <div class="col-12 col-lg-5">
      <div class="app-card p-3">
        <h6 class="fw-bold mb-3">Keterangan Point:</h6>
        <ul class="small mb-0">
          <li>Hadir Apel <span class="text-success">+1</span></li>
          <li>Cuti <span class="text-secondary">+0</span></li>
          <li>Tugas Luar <span class="text-secondary">+0</span></li>
          <li>Sakit <span class="text-secondary">+0</span></li>
          <li>Terlambat tanpa alasan <span class="text-danger">-5</span>, dengan alasan <span class="text-warning">-3</span></li>
          <li>Izin tidak masuk kantor <span class="text-secondary">+0</span></li>
        </ul>
      </div>
    </div>

    <div class="col-12 col-lg-7">
      <div class="app-card p-3">
        <h6 class="fw-bold mb-3">Rekap per Bidang</h6>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead class="table-light">
              <tr>
                <th>Bidang</th>
                <th class="text-center">Jumlah Pegawai</th>
                <th class="text-center">Hadir</th>
                <th class="text-center">Cuti</th>
                <th class="text-center">Sakit</th>
                <th class="text-center">Tugas Luar</th>
                <th class="text-center">Terlambat</th>
                <th class="text-center">Izin</th>
              </tr>
            </thead>
            <tbody>
              @foreach($daftarBidang as $b)
                @php $r = $rekapPerBidang[$b->bidang] ?? null; @endphp
                <tr>
                  <td>{{ $b->bidang }}</td>
                  <td class="text-center">{{ $b->jumlah_pegawai }}</td>
                  <td class="text-center">{{ $r->hadir ?? 0 }}</td>
                  <td class="text-center">{{ $r->cuti ?? 0 }}</td>
                  <td class="text-center">{{ $r->sakit ?? 0 }}</td>
                  <td class="text-center">{{ $r->tugas_luar ?? 0 }}</td>
                  <td class="text-center">{{ $r->terlambat ?? 0 }}</td>
                  <td class="text-center">{{ $r->izin ?? 0 }}</td>
                </tr>
              @endforeach
            </tbody>

          </table>
        </div>
      </div>
    </div>
  </div>

  {{-- Log absensi user --}}
  <div class="app-card p-3 mt-4">
    <h6 class="fw-bold mb-3">Log Absensi Terbaru</h6>
    <div class="table-responsive">
      <table class="table table-striped table-sm align-middle">
        <thead class="table-light">
          <tr>
            <th>Tanggal</th><th>Jam</th><th>Status</th><th>Alasan</th>
          </tr>
        </thead>
        <tbody>
          @forelse($log as $row)
            <tr>
              <td>{{ $row->tanggal }}</td>
              <td>{{ $row->jam }}</td>
              <td>{{ $row->status }}</td>
              <td>{{ $row->alasan }}</td>
            </tr>
          @empty
            <tr><td colspan="4" class="text-center text-muted">Belum ada data</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- Bottom nav --}}
<nav class="bottom-nav mt-4">
  <div class="container">
    <ul class="nav justify-content-around py-2">
      <li class="nav-item"><a class="nav-link active" href="#"><i class="bi bi-house-door me-1"></i> Home</a></li>
      <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-graph-up me-1"></i> Statistik</a></li>
      <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-bell me-1"></i> Notifikasi</a></li>
      <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-person me-1"></i> Account</a></li>
    </ul>
  </div>
</nav>

{{-- Modal input absen --}}
<div class="modal fade" id="absenModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ route('absen.store') }}" onsubmit="return lockSubmit(this)">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Input Absensi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="statusField" name="status" value="Hadir">

          <div class="mb-3">
            <label class="form-label">Status</label>
            <input class="form-control" id="statusPreview" value="Hadir" disabled>
          </div>

          <div class="mb-2">
            <label class="form-label" id="alasanLabel">Alasan (opsional)</label>
            <input class="form-control" id="alasanInput" name="alasan" placeholder="Tulis alasan bila diperlukan">
            <div class="form-text d-none" id="terlambatHint">
              Untuk <b>Terlambat</b>: alasan <b>wajib</b>. Poin <b>-3</b> jika ada alasan, <b>-5</b> jika kosong.
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Batal</button>
          <button class="btn btn-brand" id="submitBtn" type="submit">
            <span class="btn-text">Simpan</span>
            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
  function setStatus(s){
    const field   = document.getElementById('statusField');
    const preview = document.getElementById('statusPreview');
    const alasan  = document.getElementById('alasanInput');
    const label   = document.getElementById('alasanLabel');
    const hint    = document.getElementById('terlambatHint');

    field.value = s;
    preview.value = s;

    if (s === 'Terlambat') {
      alasan.setAttribute('required','required');
      label.textContent = 'Alasan (wajib untuk Terlambat)';
      hint.classList.remove('d-none');
      alasan.placeholder = 'Contoh: macet, ban bocor, antar anak, dsb.';
    } else {
      alasan.removeAttribute('required');
      label.textContent = 'Alasan (opsional)';
      hint.classList.add('d-none');
      alasan.placeholder = 'Tulis alasan bila diperlukan';
    }
  }

  // cegah double submit
  function lockSubmit(form){
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.querySelector('.btn-text').classList.add('d-none');
    btn.querySelector('.spinner-border').classList.remove('d-none');
    return true;
  }
</script>
@endpush
@endsection