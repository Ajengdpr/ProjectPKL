@extends('layouts.admin')
@section('title','Users')

@once
<style>
  /* ===== Page container ===== */
  .users-page{
    max-width:1100px;
    padding-bottom:8px; /* ruang kecil di bawah konten */
  }

  /* ===== Card look ===== */
  .users-page .app-card{
    border:1px solid #eef2f7; border-radius:16px; background:#fff;
    box-shadow:0 8px 22px rgba(16,24,40,.06);
  }

  /* ===== Toolbar ===== */
  .users-page .toolbar .form-select,
  .users-page .toolbar .form-control{min-height:44px}

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

  .action-stack{display:flex; justify-content:flex-end; gap:.6rem; flex-wrap:wrap}

  /* Hanya ikon di dalam tombol/link yang distandardkan ukurannya */
  .users-page .btn .bi,
  .users-page a .bi { font-size: 1rem; line-height: 1; }
  .users-page .btn svg,
  .users-page a svg { width: 1em; height: 1em; }

  /* Spacer agar pagination tidak ketutup bottom-nav */
  .users-page .table-footer { padding: 12px 16px 76px; } /* 76px ≈ tinggi bottom-nav */
</style>
@endonce

@section('content')
<div class="container-xxl users-page">


  {{-- Header --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <h1 class="h4 fw-bold mb-0">Manajemen Pegawai</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreate">
      <i class="bi bi-plus-lg me-1"></i> Tambah Pegawai
    </button>
  </div>

  {{-- Alerts --}}
  @if(session('ok'))
    <div class="alert alert-success">{{ session('ok') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  {{-- Toolbar --}}
  <div class="app-card p-3 mb-3 toolbar">
    <form method="get" class="row g-2 align-items-center">
      <div class="col-12 col-md-3">
        <select name="bidang" class="form-select">
          <option value="">Semua Bidang</option>
          @foreach($listBidang as $b)
            <option value="{{ $b }}" @selected($bidang===$b)>{{ strtoupper($b) }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-12 col-md-6">
        <input type="text" name="q" class="form-control"
               placeholder="Cari nama / username / jabatan / bidang..." value="{{ $q }}">
      </div>
      <div class="col-12 col-md-3 d-grid d-md-flex gap-2">
        <button class="btn btn-outline-secondary"><i class="bi bi-funnel me-1"></i> Terapkan</button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-dark">Reset</a>
      </div>
    </form>
  </div>

  {{-- Table --}}
  <div class="app-card p-0">
    <div class="table-responsive">
      <table class="table align-middle table-hover">
        <thead>
          <tr>
            <th style="width:56px"></th>
            <th>Nama</th>
            <th class="d-none d-lg-table-cell">Username</th>
            <th>Bidang</th>
            <th class="d-none d-xl-table-cell">Jabatan</th>
            <th style="width:260px" class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
        @forelse($users as $u)
          <tr>
            <td>
              @php $foto = $u->foto ? asset('storage/'.$u->foto) : asset('img/default-avatar.jpg'); @endphp
              <img src="{{ $foto }}" class="avatar" alt="avatar"
                   onerror="this.src='{{ asset('img/default-avatar.jpg') }}'">
            </td>
            <td class="fw-semibold">{{ $u->nama }}</td>
            <td class="d-none d-lg-table-cell text-body-secondary">{{ $u->username }}</td>
            <td>{{ $u->bidang ?: '-' }}</td>
            <td class="d-none d-xl-table-cell">{{ $u->jabatan ?: '-' }}</td>
            <td class="text-end">
              <div class="action-stack">
                {{-- Edit (modal) --}}
                <button
                  class="btn-chip btn-chip-primary"
                  data-bs-toggle="modal" data-bs-target="#modalEdit"
                  data-id="{{ $u->id }}"
                  data-nama="{{ $u->nama }}"
                  data-username="{{ $u->username }}"
                  data-bidang="{{ $u->bidang }}"
                  data-jabatan="{{ $u->jabatan }}"
                >
                  <i class="bi bi-pencil-square"></i> Edit
                </button>

                {{-- Hapus (modal konfirmasi) --}}
                <button
                  class="btn-chip btn-chip-danger"
                  data-bs-toggle="modal" data-bs-target="#confirmModal"
                  data-route="{{ route('admin.users.destroy',$u) }}"
                  data-method="delete"
                  data-title="Hapus Pegawai"
                  data-message="Hapus pengguna ini? Tindakan tidak dapat dibatalkan."
                >
                  <i class="bi bi-trash"></i> Hapus
                </button>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center text-body-secondary py-4">Tidak ada data.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination + spacer --}}
    <div class="table-footer">
      {{ $users->withQueryString()->links() }}
    </div>
  </div>
</div>

{{-- =============== Modal: Create =============== --}}
<div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" method="post" action="{{ route('admin.users.store') }}">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title">Tambah Pegawai</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-2">
          <div class="col-12">
            <label class="form-label">Nama</label>
            <input name="nama" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Username</label>
            <input name="username" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Password</label>
            <input name="password" type="password" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Bidang</label>
            <input name="bidang" class="form-control">
          </div>
          <div class="col-md-6">
            <label class="form-label">Jabatan</label>
            <input name="jabatan" class="form-control">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan</button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
      </div>
    </form>
  </div>
</div>

{{-- =============== Modal: Edit (reset di sini) =============== --}}
<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" id="formEdit">
        @csrf @method('put')
        <div class="modal-header">
          <h5 class="modal-title">Edit Pegawai</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-2">
            <div class="col-12">
              <label class="form-label">Nama</label>
              <input name="nama" id="edit-nama" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Username</label>
              <input name="username" id="edit-username" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Password (kosongkan jika tidak diubah)</label>
              <input name="password" type="password" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Bidang</label>
              <input name="bidang" id="edit-bidang" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Jabatan</label>
              <input name="jabatan" id="edit-jabatan" class="form-control">
            </div>
          </div>
        </div>
        <div class="modal-footer d-flex justify-content-between gap-2">
          {{-- Reset password (konfirmasi) --}}
          <button type="button" class="btn btn-outline-warning" id="btnResetPwd"
                  data-bs-toggle="modal" data-bs-target="#confirmModal"
                  data-title="Reset Password"
                  data-message="Reset password pengguna ini ke 123456?"
                  data-method="post"
                  data-route="">
            <i class="bi bi-key me-1"></i> Reset Password
          </button>

          <div class="d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan</button>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
          </div>
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
/* ==== Isi modal edit dinamis ==== */
document.getElementById('modalEdit')?.addEventListener('show.bs.modal', function (event) {
  const btn = event.relatedTarget;
  const id = btn.getAttribute('data-id');

  // Isi field
  document.getElementById('edit-nama').value      = btn.getAttribute('data-nama')     || '';
  document.getElementById('edit-username').value  = btn.getAttribute('data-username') || '';
  document.getElementById('edit-bidang').value    = btn.getAttribute('data-bidang')   || '';
  document.getElementById('edit-jabatan').value   = btn.getAttribute('data-jabatan')  || '';

  // Action form edit
  document.getElementById('formEdit').action = "{{ url('admin/users') }}/" + id;

  // Route reset password di tombol modal
  const resetBtn = document.getElementById('btnResetPwd');
  resetBtn.setAttribute('data-route', "{{ url('admin/users') }}/" + id + "/reset-password");
});

/* ==== Modal konfirmasi universal (hapus / reset) ==== */
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
</script>
@endsection