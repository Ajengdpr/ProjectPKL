@extends('layouts.admin')
@section('title', 'Manajemen Absensi')

@push('head')
<style>
    :root {
        --primary-blue: #0d6efd;
        --border-color: #f1f5f9;
        --bg-light: #f8fafc;
        --text-dark: #1e293b;
        --text-muted: #64748b;
        --section-gap: 2rem;
    }

    body {
        background-color: #f1f5f9;
    }

    .absensi-page-container {
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

    /* Toolbar Card */
    .toolbar-card {
        background: white;
        border-radius: 20px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        padding: 1.5rem;
        margin-bottom: var(--section-gap);
    }

    .form-label {
        font-weight: 700;
        color: var(--text-muted);
        font-size: 0.75rem;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
    }

    .form-control, .form-select {
        border-radius: 12px;
        padding: 0.6rem 1rem;
        border-color: #e2e8f0;
        background-color: var(--bg-light);
        font-weight: 500;
        font-size: 0.9rem;
    }

    /* Table Styling */
    .table-card {
        background: white;
        border-radius: 24px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
        overflow: hidden;
        margin-bottom: var(--section-gap);
    }

    .table thead th {
        background: #f8fafc;
        border: none;
        color: #64748b;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 1.25rem 1rem;
        font-weight: 800;
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

    /* Badge Status */
    .badge-status {
        padding: 0.5rem 0.85rem;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.7rem;
        text-transform: uppercase;
    }

    .status-hadir { background: #f0fdf4; color: #16a34a; }
    .status-terlambat { background: #fffbeb; color: #d97706; }
    .status-izin { background: #eff6ff; color: #2563eb; }
    .status-sakit { background: #fef2f2; color: #ef4444; }
    .status-cuti { background: #faf5ff; color: #8b5cf6; }
    .status-tugas_luar { background: #f1f5f9; color: #475569; }
    .status-alpha { background: #fef2f2; color: #b91c1c; }

    /* Badge Approval */
    .badge-approval {
        padding: 0.35rem 0.6rem;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.6rem;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        margin-top: 4px;
    }
    .approval-approved { background: #dcfce7; color: #15803d; }
    .approval-pending { background: #fef9c3; color: #a16207; }
    .approval-rejected { background: #fee2e2; color: #b91c1c; }

    /* Action Buttons */
    .btn-action {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        border: none;
    }
    .btn-edit { background: #f0f7ff; color: #0d6efd; }
    .btn-edit:hover { background: #0d6efd; color: white; transform: translateY(-2px); }
    .btn-delete { background: #fef2f2; color: #ef4444; }
    .btn-delete:hover { background: #ef4444; color: white; transform: translateY(-2px); }
    .btn-approve { background: #f0fdf4; color: #16a34a; }
    .btn-approve:hover { background: #16a34a; color: white; transform: translateY(-2px); }
    .btn-reject { background: #fff1f2; color: #e11d48; }
    .btn-reject:hover { background: #e11d48; color: white; transform: translateY(-2px); }

    .section-title {
        font-weight: 800;
        font-size: 1.1rem;
        color: var(--text-dark);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .section-title::before {
        content: "";
        width: 4px;
        height: 20px;
        background: var(--primary-blue);
        border-radius: 10px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-md-4 absensi-page-container">
    {{-- Header --}}
    <div class="header-section">
        <div class="header-content">
            <h3>Manajemen Absensi</h3>
            <p>Monitor dan kelola riwayat absensi seluruh pegawai secara realtime.</p>
        </div>
        <button type="button" class="btn btn-white bg-white text-primary rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalExportCSV">
            <i class="bi bi-file-earmark-spreadsheet-fill me-2"></i> Export CSV
        </button>
    </div>

    {{-- Filter Toolbar --}}
    <div class="toolbar-card">
        <form method="get">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Pegawai</label>
                    <select name="user_id" class="form-select">
                        <option value="">-- Semua Pegawai --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" @selected(request('user_id')==$u->id)>{{ $u->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">-- Semua Status --</option>
                        @foreach(['hadir'=>'Hadir','terlambat'=>'Terlambat','izin'=>'Izin','sakit'=>'Sakit','cuti'=>'Cuti','tugas_luar'=>'Tugas Luar','alpha'=>'Tanpa Keterangan'] as $key=>$label)
                            <option value="{{ $key }}" @selected(request('status')==$key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold rounded-3">Cari</button>
                    <a href="{{ route('admin.absensi.index') }}" class="btn btn-outline-secondary w-100 fw-bold rounded-3">Reset</a>
                </div>
            </div>
        </form>
    </div>

    {{-- Main Table --}}
    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Hari / Tanggal</th>
                        <th class="ps-4">Pegawai</th>
                        <th class="text-center">Status</th>
                        <th>Keterangan</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($absensi as $a)
                        @php $statusKey = str_replace(' ', '_', strtolower($a->status)); @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ \Carbon\Carbon::parse($a->tanggal)->locale('id')->isoFormat('dddd') }}</div>
                                <div class="small text-dark">{{ \Carbon\Carbon::parse($a->tanggal)->format('d M Y') }}</div>
                            </td>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    @php $foto = $a->user && $a->user->foto ? asset('storage/'.$a->user->foto).'?v='.time() : asset('img/default-avatar.jpg'); @endphp
                                    <img src="{{ $foto }}" class="user-avatar" alt="Avatar" onerror="this.src='{{ asset('img/default-avatar.jpg') }}'">
                                    <div>
                                        <div class="user-name">{{ $a->user->nama ?? '-' }}</div>
                                        <div class="user-meta text-primary fw-medium">{{ ($a->user->role ?? '') === 'admin' ? 'Administrator' : 'Pegawai' }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="text-center">
                                @php 
                                    $origStatus = $a->status;
                                    $showStatus = $origStatus;
                                    $approvalClass = 'approval-pending';
                                    $approvalLabel = 'Menunggu';
                                    $approvalIcon = 'bi-clock-history';

                                    if ($a->is_approved) {
                                        $approvalClass = 'approval-approved';
                                        $approvalLabel = 'Disetujui';
                                        $approvalIcon = 'bi-check-circle-fill';
                                    } elseif ($a->is_rejected) {
                                        $approvalClass = 'approval-rejected';
                                        $approvalLabel = 'Ditolak';
                                        $approvalIcon = 'bi-x-circle-fill';
                                        $showStatus = 'Tanpa Keterangan';
                                    }
                                    $statusKey = str_replace(' ', '_', strtolower($showStatus));
                                @endphp

                                <div class="d-flex flex-column align-items-center">
                                    <span class="badge-status status-{{ $statusKey }}">
                                        {{ $showStatus }}
                                    </span>
                                    <span class="badge-approval {{ $approvalClass }}">
                                        <i class="bi {{ $approvalIcon }}"></i> {{ $approvalLabel }}
                                    </span>
                                    @if($a->is_rejected)
                                        <small class="text-muted mt-1" style="font-size: 0.6rem;">({{ strtoupper($origStatus) }})</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="small text-dark">{{ $a->alasan ?: '-' }}</div>
                                @if($a->berkas)
                                    <a href="{{ asset('storage/' . $a->berkas) }}" target="_blank" class="badge bg-light text-primary border mt-1 fw-normal text-decoration-none">
                                        <i class="bi bi-paperclip"></i> Lihat Berkas
                                    </a>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    @if(!$a->is_approved && !$a->is_rejected)
                                        <button class="btn-action btn-approve" data-bs-toggle="modal" data-bs-target="#confirmModal"
                                            data-route="{{ route('absen.approve', $a) }}" data-method="post"
                                            data-title="Setujui Absensi" data-message="Setujui permohonan <strong>{{ $a->status }}</strong> oleh <strong>{{ $a->user->nama ?? 'Pegawai' }}</strong>?">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button class="btn-action btn-reject" data-bs-toggle="modal" data-bs-target="#confirmModal"
                                            data-route="{{ route('absen.reject', $a) }}" data-method="post"
                                            data-title="Tolak Absensi" data-message="Tolak permohonan <strong>{{ $a->status }}</strong> oleh <strong>{{ $a->user->nama ?? 'Pegawai' }}</strong>?">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    @endif
                                    <button class="btn-action btn-edit" data-bs-toggle="modal" data-bs-target="#modalEditAbsensi"
                                        data-id="{{ $a->id }}" data-tanggal="{{ \Carbon\Carbon::parse($a->tanggal)->format('Y-m-d') }}"
                                        data-status="{{ $a->status }}" data-alasan="{{ $a->alasan }}">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    <button class="btn-action btn-delete" data-bs-toggle="modal" data-bs-target="#confirmModal"
                                        data-route="{{ route('admin.absensi.destroy',$a) }}" data-method="delete"
                                        data-title="Hapus Absensi" data-message="Hapus log absensi <strong>{{ $a->user->nama ?? 'Pegawai' }}</strong>?">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-5 text-muted fw-bold">Tidak ada riwayat absensi ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-4 border-top bg-light-subtle">
            {{ $absensi->withQueryString()->links() }}
        </div>
    </div>

    {{-- Manual Input Section --}}
    <div class="section-title">Input Absensi Manual</div>
    <div class="toolbar-card">
        <form method="post" action="{{ route('admin.absensi.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label">Pilih Pegawai</label>
                    <select name="user_id" class="form-select" required>
                        <option value="">-- Pilih Pegawai --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status Kehadiran</label>
                    <select name="status" class="form-select" required>
                        @foreach(\App\Models\Absensi::getStatuses() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tanggal Absensi</label>
                    <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Jam Absensi (WITA)</label>
                    <input type="time" name="jam" class="form-control" value="{{ now()->format('H:i') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Alasan / Keterangan</label>
                    <input name="alasan" class="form-control" placeholder="Contoh: Rapat dinas, sakit, dll.">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Unggah Berkas Bukti</label>
                    <input type="file" name="berkas" class="form-control">
                </div>
                <div class="col-12 mt-2 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary px-5 fw-bold rounded-pill shadow-sm">
                        <i class="bi bi-save2-fill me-2"></i> Simpan Absensi
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div class="modal fade" id="modalEditAbsensi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4">
            <form method="post" id="formEditAbsensi" enctype="multipart/form-data">
                @csrf @method('put')
                <div class="modal-header border-0">
                    <h5 class="fw-bold mb-0">Edit Log Absensi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12"><label class="form-label">Tanggal</label><input type="date" name="tanggal" id="edit-tanggal" class="form-control" required></div>
                        <div class="col-12">
                            <label class="form-label">Status</label>
                            <select name="status" id="edit-status" class="form-select" required>
                                <option value="hadir">Hadir</option><option value="terlambat">Terlambat</option><option value="izin">Izin</option><option value="sakit">Sakit</option><option value="cuti">Cuti</option><option value="tugas_luar">Tugas Luar</option><option value="alpha">Tanpa Keterangan</option>
                            </select>
                        </div>
                        <div class="col-12"><label class="form-label">Keterangan</label><textarea name="alasan" id="edit-alasan" class="form-control" rows="3"></textarea></div>
                        <div class="col-12"><label class="form-label">Update Berkas</label><input type="file" name="berkas" class="form-control"></div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
                    <button class="btn btn-primary rounded-pill px-4 fw-bold">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Export CSV --}}
<div class="modal fade" id="modalExportCSV" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg p-4 text-center rounded-4">
            <h5 class="fw-bold mb-3 text-primary">Export Laporan</h5>
            <input type="month" id="export-month-input" class="form-control form-control-lg text-center mb-4" value="{{ now()->format('Y-m') }}">
            <div class="d-grid gap-2">
                <button type="button" id="btn-do-export" class="btn btn-primary rounded-pill fw-bold">Download CSV</button>
                <button type="button" class="btn btn-light rounded-pill fw-bold" data-bs-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Konfirmasi --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <form class="modal-content shadow-lg text-center p-4 rounded-4 border-0" method="post" id="confirmForm">
            @csrf <input type="hidden" id="spoofMethod" value="">
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
const confirmModal = document.getElementById('confirmModal');
confirmModal?.addEventListener('show.bs.modal', function (event) {
    const btn = event.relatedTarget;
    document.getElementById('confirmTitle').textContent = btn.getAttribute('data-title') || 'Konfirmasi';
    document.getElementById('confirmText').innerHTML = btn.getAttribute('data-message') || 'Apakah Anda yakin?';
    const form = document.getElementById('confirmForm');
    form.action = btn.getAttribute('data-route');
    const method = (btn.getAttribute('data-method') || 'post').toLowerCase();
    const spoof = document.getElementById('spoofMethod');
    if (method === 'delete') { spoof.setAttribute('name','_method'); spoof.value = 'delete'; }
    else { spoof.removeAttribute('name'); spoof.value = ''; }
});

const modalEditAbsensi = document.getElementById('modalEditAbsensi');
modalEditAbsensi?.addEventListener('show.bs.modal', function (event) {
    const btn = event.relatedTarget;
    const id = btn.getAttribute('data-id');
    document.getElementById('edit-tanggal').value = btn.getAttribute('data-tanggal') || '';
    document.getElementById('edit-status').value = btn.getAttribute('data-status') || 'hadir';
    document.getElementById('edit-alasan').value = btn.getAttribute('data-alasan') || '';
    const form = document.getElementById('formEditAbsensi');
    form.action = "{{ url('admin/absensi') }}/" + id;
});

document.addEventListener('DOMContentLoaded', function() {
    const doExportBtn = document.getElementById('btn-do-export');
    const monthInput = document.getElementById('export-month-input');
    const exportModalElement = document.getElementById('modalExportCSV');
    if (doExportBtn && monthInput && exportModalElement) {
        doExportBtn.addEventListener('click', function() {
            const bulan = monthInput.value;
            if (!bulan) return alert('Pilih bulan.');
            const firstDay = new Date(bulan + '-02');
            const lastDay = new Date(firstDay.getFullYear(), firstDay.getMonth() + 1, 0);
            const formatDate = (date) => {
                const d = new Date(date.getTime() - (date.getTimezoneOffset() * 60000));
                return d.toISOString().split('T')[0];
            }
            const url = new URL("{{ route('admin.absensi.export.csv') }}");
            url.searchParams.append('from', formatDate(firstDay));
            url.searchParams.append('to', formatDate(lastDay));
            window.location.href = url.toString();
            bootstrap.Modal.getInstance(exportModalElement).hide();
        });
    }
});
</script>
@endpush
@endsection
