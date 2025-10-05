@extends('layouts.admin')
@section('title','Manajemen Absensi')

@once
<style>
  /* ===== Page container ===== */
  .users-page{
    max-width:1100px;
    padding-bottom:8px; 
  }

  /* ===== Card look ===== */
  .users-page .app-card{
    border:1px solid #eef2f7; border-radius:16px; background:#fff;
    box-shadow:0 8px 22px rgba(16,24,40,.06);
  }

  /* ===== Toolbar ===== */
  .users-page .toolbar .form-select,
  .users-page .toolbar .form-control{min-height:44px}
  .users-page .toolbar .btn{min-height:44px}

  /* ===== Table ===== */
  .users-page .table{margin-bottom:0}
  .users-page .table thead th{
    font-weight:700; color:#6b7280; font-size:.85rem; letter-spacing:.02em;
    background:#f8fafc; border-top:0; border-bottom:1px solid #eef2f7;
  }
  .users-page .table tbody td{vertical-align:middle; border-bottom:1px solid #eef2f7;}
  .users-page .avatar{
    width:40px; height:40px; border-radius:50%; object-fit:cover; background:#f3f4f6
  }

  /* ===== Uniform action buttons (chip) ===== */
  .btn-chip{
    display:inline-flex; align-items:center; justify-content:center; gap:.45rem;
    min-width:110px; min-height:40px; padding:.45rem .8rem;
    font-weight:600; border-radius:10px;
    border:1px solid rgba(2,6,23,.08); background:#fff; color:#0f172a;
  }
  .btn-chip:hover{background:#f8fafc}
  .btn-chip .bi{font-size:1rem!important; line-height:1}

  .btn-chip-primary{border-color:rgba(13,110,253,.28); color:#0d6efd; background:rgba(13,110,253,.06)}
  .btn-chip-primary:hover{background:rgba(13,110,253,.12)}

  .btn-chip-danger{border-color:rgba(220,53,69,.28); color:#b42318; background:rgba(220,53,69,.08)}
  .btn-chip-danger:hover{background:rgba(220,53,69,.16)}

  .btn-chip-success{border-color:rgba(25,135,84,.28); color:#198754; background:rgba(25,135,84,.08)}
  .btn-chip-success:hover{background:rgba(25,135,84,.16)}

  .action-stack{display:flex; justify-content:flex-end; gap:.6rem; flex-wrap:wrap}

  /* Spacer agar pagination tidak ketutup bottom-nav */
  .users-page .table-footer { padding: 12px 16px 76px; }

  /* ===== Style Teks Status Kustom ===== */
.status-hadir { color: var(--bs-success-dark, #198754) !important; }
.status-terlambat { color: var(--bs-warning-dark, #ffc107) !important; font-weight: 600; }
.status-sakit { color: var(--bs-info-dark, #0dcaf0) !important; }
.status-izin { color: var(--bs-primary-dark, #0d6efd) !important; }
.status-alpha { color: var(--bs-danger-dark, #dc3545) !important; font-weight: 600; }
.status-tugas-luar { color: var(--bs-secondary-dark, #6c757d) !important; }
</style>
@endonce

@section('content')
<div class="container-xxl users-page">

  {{-- Header --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <h1 class="h4 fw-bold mb-0">Manajemen Absensi</h1>
    <a href="{{ route('admin.absensi.export.csv', request()->only('from','to','user_id','bidang','status')) }}"
       class="btn btn-outline-secondary"><i class="bi bi-download me-1"></i>Export CSV</a>
  </div>


{{-- Toolbar Filter --}}
<div class="app-card p-3 mb-3 toolbar">
  <form method="get">
  {{-- Filter Utama yang Selalu Terlihat --}}
  <div class="row g-2 align-items-center">
    <div class="col-md-3">
      <input type="text" name="q" class="form-control" placeholder="Cari alasan atau nama pegawai..." value="{{ request('q') }}">
    </div>
    <div class="col-md-2">
      <input type="date" name="from" value="{{ request('from') }}" class="form-control" title="Dari Tanggal">
    </div>
    <div class="col-md-2">
      <input type="date" name="to" value="{{ request('to') }}" class="form-control" title="Sampai Tanggal">
    </div>

    {{-- DIUBAH: ms-auto mendorong grup ini ke kanan & col-md-auto membuat lebarnya pas --}}
    <div class="col-md-auto ms-auto d-flex gap-2">
      <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#advancedFilter" aria-expanded="false" aria-controls="advancedFilter">
        <i class="bi bi-sliders me-1"></i> Filter Lanjutan
      </button>
      <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i> Cari</button>
      <a href="{{ route('admin.absensi.index') }}" class="btn btn-outline-dark">Reset</a>
    </div>
  </div>

    {{-- Filter Lanjutan yang Tersembunyi --}}
    <div class="collapse mt-3" id="advancedFilter">
      <div class="row g-2">
        <div class="col-md-6">
          <label class="form-label">Filter Berdasarkan Pegawai</label>
          <select name="user_id" class="form-select">
            <option value="">-- Semua Pegawai --</option>
            @foreach($users as $u)
              <option value="{{ $u->id }}" @selected(request('user_id')==$u->id)>{{ $u->nama }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Filter Berdasarkan Status</label>
          <select name="status" class="form-select">
            <option value="">-- Semua Status --</option>
            @foreach(\App\Models\Absensi::getStatuses() as $key=>$label)
              <option value="{{ $key }}" @selected(request('status')==$key)>{{ $label }}</option>
            @endforeach
          </select>
        </div>
      </div>
    </div>
  </form>
</div>

  {{-- Tabel Absensi --}}
  <div class="app-card p-0">
    <div class="table-responsive">
      <table class="table align-middle table-hover mb-0">
        <thead>
          <tr>
            <th style="width:130px;">Tanggal</th>
            <th>Nama Pegawai</th>
            <th style="width:120px;">Status</th>
            <th>Alasan</th>
            <th style="width:260px" class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
      @php
        $badgeStyles = function($status) {
            // Ubah status menjadi huruf kecil dan hapus spasi di awal/akhir
            $safeStatus = strtolower(trim($status ?? ''));

            $styles = match($safeStatus) {
                'hadir'       => ['class' => 'bg-success-subtle', 'style' => 'color: #146c43 !important;'],
                'terlambat'   => ['class' => 'bg-warning-subtle', 'style' => 'color: #e59400 !important; font-weight: 600;'],
                'sakit'       => ['class' => 'bg-info-subtle',    'style' => 'color: #087990 !important;'],
                'izin'        => ['class' => 'bg-primary-subtle', 'style' => 'color: #0a58ca !important;'],
                'tugas luar'  => ['class' => 'bg-secondary-subtle', 'style' => 'color: #41464b !important;'],
                'alpha'       => ['class' => 'bg-danger-subtle',  'style' => 'color: #b02a37 !important; font-weight: 600;'],
                default       => ['class' => 'bg-light',          'style' => 'color: #000 !important;']
            };
            return $styles;
        };
      @endphp
          @forelse($absensi as $a)
            <tr>
            <td>{{ \Carbon\Carbon::parse($a->tanggal)->format('d/m/Y') }}</td>
              <td>
                <div class="d-flex align-items-center gap-3">
                  @php $foto = $a->user && $a->user->foto ? asset('storage/'.$a->user->foto) : asset('img/default-avatar.jpg'); @endphp
                  <img src="{{ $foto }}" class="avatar" alt="avatar"
                       onerror="this.src='{{ asset('img/default-avatar.jpg') }}'">
                  <span class="fw-medium">{{ $a->user->nama ?? '-' }}</span>
                </div>
              </td>
              <td>
              @php $badge = $badgeStyles($a->status); @endphp
              <span class="badge rounded-pill {{ $badge['class'] }}" style="{{ $badge['style'] }}">
                {{ \App\Models\Absensi::getStatuses()[$a->status] ?? strtoupper($a->status) }}
              </span>
            </td>
              <td>{{ $a->alasan ?: '-' }}</td>
              <td class="text-end">
                <div class="action-stack">
                  <a href="{{ route('admin.absensi.export.user.csv', ['user_id' => $a->user_id, 'bulan' => \Carbon\Carbon::parse($a->tanggal)->format('Y-m')]) }}" class="btn-chip btn-chip-success">
                    <i class="bi bi-download"></i> Export
                  </a>
                  <button
                    class="btn-chip btn-chip-primary"
                    data-bs-toggle="modal" data-bs-target="#modalEditAbsensi"
                    data-id="{{ $a->id }}"
                    data-tanggal="{{ \Carbon\Carbon::parse($a->tanggal)->format('Y-m-d') }}"
                    data-status="{{ $a->status }}"
                    data-alasan="{{ $a->alasan }}"
                  >
                    <i class="bi bi-pencil-square"></i> Edit
                  </button>
                  <button
                    class="btn-chip btn-chip-danger"
                    data-bs-toggle="modal" data-bs-target="#confirmModal"
                    data-route="{{ route('admin.absensi.destroy',$a) }}"
                    data-method="delete"
                    data-title="Hapus Absensi"
                    data-message="Hapus data absensi ini? Tindakan tidak dapat dibatalkan."
                  >
                    <i class="bi bi-trash"></i> Hapus
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-body-secondary py-4">Tidak ada data.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination + spacer --}}
    <div class="table-footer">
      {{ $absensi->links() }}
    </div>
  </div>

  {{-- Input Manual --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-4 mb-3">
    <h2 class="h5 fw-bold mb-0">Input Absensi Manual</h2>
  </div>
  <div class="app-card p-3">
    <form method="post" action="{{ route('admin.absensi.store') }}" class="row g-2">
      @csrf
      <div class="col-12 col-md-3">
        <select name="user_id" class="form-select" required>
          <option value="">-- Pilih Pegawai --</option>
          @foreach($users as $u)
            <option value="{{ $u->id }}">{{ $u->nama }} ({{ $u->username }})</option>
          @endforeach
        </select>
      </div>
      <div class="col-6 col-md-2">
        <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
      </div>
      <div class="col-6 col-md-2">
        <input type="time" name="jam" class="form-control" value="{{ now()->format('H:i') }}" required>
      </div>
      <div class="col-6 col-md-2">
        <select name="status" class="form-select" required>
          @foreach(\App\Models\Absensi::getStatuses() as $key => $label)
          <option value="{{ $key }}">{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-12 col-md-2">
        <input name="alasan" class="form-control" placeholder="Alasan (opsional)">
      </div>
      <div class="col-12 col-md-1 d-grid">
        <button class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

{{-- =============== Modal: Edit Absensi =============== --}}
<div class="modal fade" id="modalEditAbsensi" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" id="formEditAbsensi">
        @csrf
        @method('put')
        <div class="modal-header">
          <h5 class="modal-title">Edit Absensi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-2">
            <div class="col-12">
              <label class="form-label">Tanggal</label>
              <input type="date" name="tanggal" id="edit-tanggal" class="form-control" required>
            </div>
            <div class="col-12">
              <label class="form-label">Status</label>
              <select name="status" id="edit-status" class="form-select" required>
                @foreach(\App\Models\Absensi::getStatuses() as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Alasan (opsional)</label>
              <input name="alasan" id="edit-alasan" class="form-control">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- =============== Modal Konfirmasi (universal) =============== --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" method="post" id="confirmForm">
      @csrf
      <input type="hidden" id="spoofMethod" value="">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmTitle">Konfirmasi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0" id="confirmText">Apakah Anda yakin?</p>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-danger" id="confirmSubmit">Ya, Lanjutkan</button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
      </div>
    </form>
  </div>
</div>

<script>
/* ==== Modal konfirmasi universal (hapus) ==== */
const confirmModal = document.getElementById('confirmModal');
confirmModal?.addEventListener('show.bs.modal', function (event) {
  const btn     = event.relatedTarget;
  const route   = btn.getAttribute('data-route');
  const method  = (btn.getAttribute('data-method') || 'post').toLowerCase();
  const title   = btn.getAttribute('data-title') || 'Konfirmasi';
  const message = btn.getAttribute('data-message') || 'Apakah Anda yakin?';

  document.getElementById('confirmTitle').textContent = title;
  document.getElementById('confirmText').textContent  = message;

  const form = document.getElementById('confirmForm');
  form.action = route;

  const spoof = document.getElementById('spoofMethod');
  if (method === 'delete') {
    spoof.setAttribute('name','_method');
    spoof.value = 'delete';
  } else {
    spoof.removeAttribute('name');
    spoof.value = '';
  }
});

const modalEditAbsensi = document.getElementById('modalEditAbsensi');
modalEditAbsensi?.addEventListener('show.bs.modal', function (event) {
  const btn = event.relatedTarget;
  const id = btn.getAttribute('data-id');

  document.getElementById('edit-tanggal').value = btn.getAttribute('data-tanggal') || '';
  document.getElementById('edit-status').value  = btn.getAttribute('data-status')  || 'hadir';
  document.getElementById('edit-alasan').value  = btn.getAttribute('data-alasan')  || '';

  const form = document.getElementById('formEditAbsensi');
  form.action = "{{ url('admin/absensi') }}/" + id;
});
</script>
@endsection