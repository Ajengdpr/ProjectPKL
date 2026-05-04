@extends('layouts.admin')
@section('title', 'Manajemen Pegawai')

@push('head')
<style>
    :root {
        --primary-blue: #0d6efd;
        --border-color: #f1f5f9;
        --bg-light: #f8fafc;
        --section-gap: 2rem;
    }

    body {
        background-color: #f1f5f9;
    }

    .users-page-container {
        width: 100%;
        margin-top: 0;
        padding-bottom: 5rem;
    }

    /* Modern Header */
    .header-section {
        background: linear-gradient(135deg, #0d6efd 0%, #003d99 100%);
        padding: 2.5rem 3rem;
        border-radius: 24px;
        border: none;
        box-shadow: 0 10px 25px rgba(0, 61, 153, 0.15);
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--section-gap);
        color: white;
    }

    .header-content h3 {
        font-weight: 800;
        margin: 0;
        color: white;
        letter-spacing: -0.5px;
    }

    .header-content p {
        margin: 0;
        color: rgba(255, 255, 255, 0.8);
        font-weight: 500;
    }

    /* Stats & Toolbar Card */
    .toolbar-card {
        background: white;
        border-radius: 20px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        padding: 1.5rem;
        margin-bottom: var(--section-gap);
    }

    .form-control, .form-select {
        border-radius: 12px;
        padding: 0.6rem 1rem;
        border-color: #e2e8f0;
        background-color: var(--bg-light);
        font-weight: 500;
    }

    /* Modern Table */
    .table-card {
        background: white;
        border-radius: 24px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
        overflow: hidden;
    }

    .table thead th {
        background: #f8fafc;
        border: none;
        color: #64748b;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 1.25rem 1rem;
    }

    .table tbody td {
        padding: 1.25rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .user-avatar {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        object-fit: cover;
        background: #f1f5f9;
        border: 2px solid white;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }

    .user-name {
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0;
        font-size: 0.95rem;
    }

    .user-meta {
        font-size: 0.8rem;
        color: #64748b;
    }

    /* Custom Badges */
    .badge-bidang {
        background: #eff6ff;
        color: #2563eb;
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.75rem;
    }

    /* Action Buttons */
    .btn-action {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        border: none;
        font-size: 1.1rem;
    }

    .btn-edit { background: #f0f7ff; color: #0d6efd; }
    .btn-edit:hover { background: #0d6efd; color: white; transform: translateY(-2px); }

    .btn-delete { background: #fef2f2; color: #ef4444; }
    .btn-delete:hover { background: #ef4444; color: white; transform: translateY(-2px); }

    /* Modal Styling */
    .modal-content {
        border-radius: 24px;
        border: none;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    .modal-header {
        border-bottom: 1px solid #f1f5f9;
        padding: 1.5rem 2rem;
    }

    .modal-footer {
        border-top: 1px solid #f1f5f9;
        padding: 1.5rem 2rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-md-4 users-page-container">
    {{-- Header --}}
    <div class="header-section">
        <div class="header-content">
            <h3>Manajemen Pegawai</h3>
            <p>Kelola data profil, bidang, dan jabatan seluruh pegawai DLH.</p>
        </div>
        <button class="btn btn-white bg-white text-primary rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCreate">
            <i class="bi bi-person-plus-fill me-2"></i> Tambah Pegawai
        </button>
    </div>

    {{-- Alerts --}}
    @if(session('ok'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-4 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('ok') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Toolbar / Search & Filter --}}
    <div class="toolbar-card">
        <form method="get">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-bold text-muted">Cari Pegawai</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0" style="border-radius: 12px 0 0 12px;"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" class="form-control border-start-0" placeholder="Nama, username, atau jabatan..." value="{{ $q }}" style="border-radius: 0 12px 12px 0;">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">Filter Bidang</label>
                    <select name="bidang" class="form-select">
                        <option value="">-- Semua Bidang --</option>
                        @foreach($listBidang as $b)
                            <option value="{{ $b }}" @selected($bidang===$b)>{{ strtoupper($b) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-3 w-100 fw-bold">Terapkan</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary rounded-3 w-100 fw-bold">Reset</a>
                </div>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Pegawai</th>
                        <th>Bidang & Jabatan</th>
                        <th class="text-center">Username</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    @php $foto = $u->foto ? asset('storage/'.$u->foto).'?v='.time() : asset('img/default-avatar.jpg'); @endphp
                                    <img src="{{ $foto }}" class="user-avatar" alt="Avatar" onerror="this.src='{{ asset('img/default-avatar.jpg') }}'">
                                    <div>
                                        <div class="user-name">{{ $u->nama }}</div>
                                        <div class="user-meta d-flex gap-1 align-items-center">
                                            <span class="text-primary fw-medium">{{ $u->role === 'admin' ? 'Administrator' : 'Pegawai' }}</span>
                                            <span class="text-muted">•</span>
                                            <span class="badge {{ $u->level === 'kadin' ? 'bg-danger' : ($u->level === 'kabid' ? 'bg-warning text-dark' : 'bg-secondary') }} rounded-pill" style="font-size: 0.65rem;">{{ strtoupper($u->level ?? 'anggota') }}</span>
                                            @if(!($u->is_active ?? true))
                                                <span class="badge bg-dark text-white rounded-pill" style="font-size: 0.65rem;">NONAKTIF</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="mb-1"><span class="badge-bidang">{{ $u->bidang ?: 'Umum' }}</span></div>
                                <div class="small text-muted">{{ $u->jabatan ?: 'Staff' }}</div>
                            </td>
                            <td class="text-center">
                                <code class="bg-light px-2 py-1 rounded text-dark">{{ $u->username }}</code>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <button class="btn-action btn-edit" title="Edit Data"
                                        data-bs-toggle="modal" data-bs-target="#modalEdit"
                                        data-id="{{ $u->id }}"
                                        data-nama="{{ $u->nama }}"
                                        data-username="{{ $u->username }}"
                                        data-bidang="{{ $u->bidang }}"
                                        data-jabatan="{{ $u->jabatan }}"
                                        data-level="{{ $u->level ?? 'anggota' }}"
                                        data-active="{{ $u->is_active ? '1' : '0' }}">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    <button class="btn-action btn-delete" title="Hapus Pegawai"
                                        data-bs-toggle="modal" data-bs-target="#confirmModal"
                                        data-route="{{ route('admin.users.destroy',$u) }}"
                                        data-method="delete"
                                        data-title="Hapus Pegawai"
                                        data-message="Apakah Anda yakin ingin menghapus <strong>{{ $u->nama }}</strong>? Tindakan ini tidak dapat dibatalkan.">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <div class="text-muted opacity-50 mb-2"><i class="bi bi-people fs-1"></i></div>
                                <div class="fw-bold text-muted">Tidak ada data pegawai ditemukan</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-4 border-top bg-light-subtle">
            {{ $users->withQueryString()->links() }}
        </div>
    </div>
</div>

{{-- Modal Create --}}
<div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content shadow-lg border-0" method="post" action="{{ route('admin.users.store') }}">
            @csrf
            <div class="modal-header">
                <h5 class="fw-bold mb-0">Tambah Pegawai Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Nama Lengkap</label>
                        <input name="nama" class="form-control" required placeholder="Nama sesuai SK">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Username</label>
                        <input name="username" class="form-control" required placeholder="Untuk login">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kata Sandi</label>
                        <input name="password" type="password" class="form-control" required placeholder="Min. 6 Karakter">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Bidang</label>
                        <select name="bidang" class="form-select" required>
                            <option value="">-- Pilih Bidang --</option>
                            @foreach($listBidang as $b)
                                <option value="{{ $b }}">{{ strtoupper($b) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jabatan</label>
                        <input name="jabatan" class="form-control" required placeholder="Contoh: Ahli Muda">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Level Akses Hirarki</label>
                        <select name="level" class="form-select border-primary border-opacity-25" required>
                            <option value="anggota">Anggota Bidang (Staff Biasa)</option>
                            <option value="kabid">Kepala Bidang (Bisa Pantau Bidang Sendiri)</option>
                            <option value="kadin">Kepala Dinas (Bisa Pantau Semua Bidang)</option>
                        </select>
                        <div class="form-text text-primary" style="font-size: 0.75rem;">
                            <i class="bi bi-info-circle-fill me-1"></i> Level ini menentukan notifikasi dan akses statistik.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">Simpan Data</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <form method="post" id="formEdit">
                @csrf @method('put')
                <div class="modal-header">
                    <h5 class="fw-bold mb-0">Perbarui Data Pegawai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nama Lengkap</label>
                            <input name="nama" id="edit-nama" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input name="username" id="edit-username" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bidang</label>
                            <select name="bidang" id="edit-bidang" class="form-select" required>
                                <option value="">-- Pilih Bidang --</option>
                                @foreach($listBidang as $b)
                                    <option value="{{ $b }}">{{ $b }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Level Akses</label>
                            <select name="level" id="edit-level" class="form-select" required>
                                <option value="anggota">Anggota Bidang</option>
                                <option value="kabid">Kepala Bidang</option>
                                <option value="kadin">Kepala Dinas</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Jabatan</label>
                            <input name="jabatan" id="edit-jabatan" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-warning rounded-pill px-3 fw-bold" id="btnResetPwd"
                        data-bs-toggle="modal" data-bs-target="#confirmModal"
                        data-title="Reset Password"
                        data-message="Apakah Anda yakin ingin mereset password pengguna ini menjadi <strong>123456</strong>?"
                        data-method="post"
                        data-route="">
                        <i class="bi bi-key-fill me-1"></i> Reset Password
                    </button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
                        <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Konfirmasi --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <form class="modal-content shadow-lg border-0 text-center p-4" method="post" id="confirmForm">
            @csrf
            <input type="hidden" id="spoofMethod" value="">
            <div class="mb-3"><i class="bi bi-exclamation-circle text-danger display-4"></i></div>
            <h5 class="fw-bold" id="confirmTitle">Konfirmasi</h5>
            <p class="text-muted small" id="confirmText">Apakah Anda yakin?</p>
            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-danger rounded-pill fw-bold" id="confirmSubmit">Ya, Lanjutkan</button>
                <button type="button" class="btn btn-light rounded-pill fw-bold" data-bs-dismiss="modal">Batal</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('modalEdit')?.addEventListener('show.bs.modal', function (event) {
    const btn = event.relatedTarget;
    const id = btn.getAttribute('data-id');
    document.getElementById('edit-nama').value = btn.getAttribute('data-nama') || '';
    document.getElementById('edit-username').value = btn.getAttribute('data-username') || '';
    document.getElementById('edit-bidang').value = btn.getAttribute('data-bidang') || '';
    document.getElementById('edit-jabatan').value = btn.getAttribute('data-jabatan') || '';
    document.getElementById('edit-level').value = btn.getAttribute('data-level') || 'anggota';
    document.getElementById('formEdit').action = "{{ url('admin/users') }}/" + id;
    const resetBtn = document.getElementById('btnResetPwd');
    resetBtn.setAttribute('data-route', "{{ url('admin/users') }}/" + id + "/reset-password");
});

const confirmModal = document.getElementById('confirmModal');
confirmModal?.addEventListener('show.bs.modal', function (event) {
    const btn = event.relatedTarget;
    document.getElementById('confirmTitle').textContent = btn.getAttribute('data-title') || 'Konfirmasi';
    document.getElementById('confirmText').innerHTML = btn.getAttribute('data-message') || 'Apakah Anda yakin?';
    const form = document.getElementById('confirmForm');
    form.action = btn.getAttribute('data-route');
    const method = (btn.getAttribute('data-method') || 'post').toLowerCase();
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
@endpush
@endsection
