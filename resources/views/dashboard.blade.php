@extends('layouts.app')
@section('title', 'Dashboard')

{{-- LETAK PERBAIKANNYA ADA DI SINI --}}
@push('styles')
<style>
  .tile {
    display: flex;             
    flex-direction: column;    
    justify-content: center;  
    align-items: center;       
    padding: 20px;            
    min-height: 120px;         
  }
  .tile i {
    font-size: 2.5rem;         
    margin-bottom: 10px;      
  }
</style>
@endpush
{{-- BATAS AKHIR PERBAIKAN --}}

@section('content')
  <div class="container">

    {{-- Wadah untuk notifikasi kustom dari JavaScript --}}
    <div id="custom-alert-container" style="position: fixed; top: 80px; right: 20px; z-index: 1050; max-width: 350px;">
    </div>

    {{-- Alerts --}}
    @if(session('ok'))
      <div class="alert alert-success">{{ session('ok') }}</div>
    @endif
    @if(session('err'))
      <div class="alert alert-danger">{{ session('err') }}</div>
    @endif
    @if($errors->any())
      <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    {{-- Panel Info Pengguna & Lokasi --}}
    <div class="app-card p-3 mb-4">
      <div class="row g-3 text-center">
        <div class="col-6 col-md-3">
          <div class="small text-body-secondary">Poin Anda</div>
          <div class="fw-bold fs-5">{{ $user->point ?? 0 }}</div>
        </div>
        <div class="col-6 col-md-3">
          <div class="small text-body-secondary">Status Lokasi</div>
          <div id="geo-status" class="fw-bold fs-5">-</div>
        </div>
        <div class="col-6 col-md-3">
          <div class="small text-body-secondary">Jarak dari Kantor</div>
          <div id="geo-distance" class="fw-bold fs-5">-</div>
        </div>
        <div class="col-6 col-md-3">
          <div class="small text-body-secondary">Akurasi GPS</div>
          <div id="geo-accuracy" class="fw-bold fs-5">-</div>
        </div>
      </div>
    </div>
    
    @php
      // Logika baru: Pisahkan kondisi terkunci karena sudah absen dan karena waktu habis
      $absenLocked = $sudahAbsenToday ?? false;
      $hadirExpired = $hadirDisabled ?? false;
      // Gunakan variabel dari controller jika ada, jika tidak, gunakan default 16:00
      $akhirExpired = $akhirExpired ?? (now('Asia/Makassar')->format('H:i:s') > '16:00:00');
    @endphp
    

    {{-- Tiles --}}
    <div class="row g-3">
      <div class="col-12 col-md-4">
        {{-- Tombol Hadir dengan logika baru --}}
        <a id="btnHadir" class="tile cyan w-100 text-decoration-none {{ $absenLocked ? 'disabled' : '' }}"
           style="{{ $absenLocked ? 'pointer-events:none;opacity:.5' : '' }}"
           @if($absenLocked)
             {{-- Jika sudah absen, tidak ada aksi --}}
           @elseif($hadirExpired)
             {{-- Jika waktu habis, tampilkan alert --}}
             onclick="showCustomAlert('Anda melewati batas waktu hadir', 'warning')"
           @else
             {{-- Jika normal, buka modal --}}
             data-bs-toggle="modal" data-bs-target="#absenModal" onclick="setStatus('Hadir')"
           @endif >
          <i class="bi bi-person"></i><h6>Hadir</h6>
        </a>
      </div>
      <div class="col-12 col-md-4">
        <a id="btnIzin" class="tile dark w-100 text-decoration-none {{ $absenLocked ? 'disabled' : '' }}"
           style="{{ $absenLocked ? 'pointer-events:none;opacity:.5' : '' }}"
           @if($absenLocked)
             {{-- Jika sudah absen, tidak ada aksi --}}
           @elseif($akhirExpired)
             onclick="showCustomAlert('Anda melewati batas waktu absensi', 'warning')"
           @else
             data-bs-toggle="modal" data-bs-target="#absenModal" onclick="setStatus('Izin')"
           @endif >
          <i class="bi bi-phone"></i><h6>Izin</h6>
        </a>
      </div>
      <div class="col-12 col-md-4">
        <a id="btnSakit" class="tile gray w-100 text-decoration-none {{ $absenLocked ? 'disabled' : '' }}"
           style="{{ $absenLocked ? 'pointer-events:none;opacity:.5' : '' }}"
           @if($absenLocked)
             {{-- Jika sudah absen, tidak ada aksi --}}
           @elseif($akhirExpired)
             onclick="showCustomAlert('Anda melewati batas waktu absensi', 'warning')"
           @else
             data-bs-toggle="modal" data-bs-target="#absenModal" onclick="setStatus('Sakit')"
           @endif >
          <i class="bi bi-emoji-frown"></i><h6>Sakit</h6>
        </a>
      </div>
      <div class="col-12 col-md-4">
        <a id="btnTugasLuar" class="tile green w-100 text-decoration-none {{ $absenLocked ? 'disabled' : '' }}"
           style="{{ $absenLocked ? 'pointer-events:none;opacity:.5' : '' }}"
           @if($absenLocked)
             {{-- Jika sudah absen, tidak ada aksi --}}
           @elseif($akhirExpired)
             onclick="showCustomAlert('Anda melewati batas waktu absensi', 'warning')"
           @else
             data-bs-toggle="modal" data-bs-target="#absenModal" onclick="setStatus('Tugas Luar')"
           @endif >
          <i class="bi bi-airplane"></i><h6>Tugas Luar</h6>
        </a>
      </div>
      <div class="col-12 col-md-4">
        <a id="btnCuti" class="tile yellow w-100 text-decoration-none {{ $absenLocked ? 'disabled' : '' }}"
           style="{{ $absenLocked ? 'pointer-events:none;opacity:.5' : '' }}"
           @if($absenLocked)
             {{-- Jika sudah absen, tidak ada aksi --}}
           @elseif($akhirExpired)
             onclick="showCustomAlert('Anda melewati batas waktu absensi', 'warning')"
           @else
             data-bs-toggle="modal" data-bs-target="#absenModal" onclick="setStatus('Cuti')"
           @endif >
          <i class="bi bi-x-circle"></i><h6>Cuti</h6>
        </a>
      </div>
      <div class="col-12 col-md-4">
        <a id="btnTerlambat" class="tile red w-100 text-decoration-none {{ $absenLocked ? 'disabled' : '' }}"
           style="{{ $absenLocked ? 'pointer-events:none;opacity:.5' : '' }}"
           @if($absenLocked)
             {{-- Jika sudah absen, tidak ada aksi --}}
           @elseif($akhirExpired)
             onclick="showCustomAlert('Anda melewati batas waktu absensi', 'warning')"
           @else
             data-bs-toggle="modal" data-bs-target="#absenModal" onclick="setStatus('Terlambat')"
           @endif >
          <i class="bi bi-alarm"></i><h6>Terlambat</h6>
        </a>
      </div>
    </div>

{{-- Keterangan & Rekap (stacked, urutan dibalik) --}}
<div class="row mt-4 g-3">
  {{-- Rekap per Bidang (atas) --}}
  <div class="col-12">
    <div class="app-card p-3">
      
      {{-- Judul Rekap --}}
      <h6 class="fw-bold mb-1">Rekap per Bidang</h6>

      {{-- Tanggal Hari Ini --}}
      <div class="text-start mb-3">
        <strong>{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</strong>
      </div>

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
          <tfoot class="table-light">
            <tr>
              <th>Total</th>
              <td class="text-center">
                {{ $daftarBidang->sum('jumlah_pegawai') }}
              </td>
              <td class="text-center">{{ $rekapPerBidang->sum(fn($rekap) => $rekap->hadir) }}</td>
              <td class="text-center">{{ $rekapPerBidang->sum(fn($rekap) => $rekap->cuti) }}</td>
              <td class="text-center">{{ $rekapPerBidang->sum(fn($rekap) => $rekap->sakit) }}</td>
              <td class="text-center">{{ $rekapPerBidang->sum(fn($rekap) => $rekap->tugas_luar) }}</td>
              <td class="text-center">{{ $rekapPerBidang->sum(fn($rekap) => $rekap->terlambat) }}</td>
              <td class="text-center">{{ $rekapPerBidang->sum(fn($rekap) => $rekap->izin) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</div>

    {{-- Keterangan Point (bawah) --}}
    <div class="col-12 mt-4"> <div class="app-card p-3">
        <h6 class="fw-bold mb-3">Keterangan Point:</h6>
        <ul class="small mb-0">
          <li>Hadir Apel <span class="text-success">+1</span></li>
          <li>Cuti <span class="text-secondary">+0</span></li>
          <li>Tugas Luar <span class="text-secondary">+0</span></li>
          <li>Sakit <span class="text-secondary">+0</span></li>
          <li>Terlambat <span class="text-warning">-3</span></li>
          <li>Tanpa keterangan <span class="text-danger">-5</span></li>
          <li>Izin<span class="text-secondary">+0</span></li>
        </ul>
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

  <div class="modal fade" id="absenModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="{{ route('absen.store') }}" onsubmit="return lockSubmit(this)" enctype="multipart/form-data">
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
              <input class="form-control" id="alasanInput" name="alasan" placeholder="Tulis alasan bila diperlukan" style="display: none;">
            </div>
            {{-- Container untuk upload file --}}
            <div class="mb-2" id="fileUploadContainer" style="display: none;">
                <label class="form-label" id="fileLabel">Upload Surat Tugas / Bukti</label>
                <input class="form-control" type="file" id="fileInput" name="berkas" accept=".jpg, .jpeg, .png, .pdf, .doc, .docx">
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
  /**
   * Menampilkan notifikasi kustom di pojok kanan atas.
   * @param {string} message Pesan yang akan ditampilkan.
   * @param {string} type Jenis notifikasi (e.g., 'danger', 'success', 'warning').
   * @param {number} duration Durasi dalam milidetik sebelum notifikasi hilang.
   * @returns {string} ID dari elemen alert yang dibuat.
   */
  function showCustomAlert(message, type = 'danger', duration = 4000) {
    const container = document.getElementById('custom-alert-container');
    if (!container) return null;

    const alertId = 'alert-' + Date.now();
    const alertDiv = document.createElement('div');
    // Tambahkan kelas 'alert-loading' jika durasi null/0 untuk penanda
    const loadingClass = !duration ? ' alert-loading' : '';
    alertDiv.id = alertId;
    alertDiv.className = `alert alert-${type} alert-dismissible fade show shadow-sm`;
    alertDiv.setAttribute('role', 'alert');
    
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    container.appendChild(alertDiv);

    if (duration) setTimeout(() => bootstrap.Alert.getOrCreateInstance(alertDiv)?.close(), duration);

    return alertId;
  }

  /**
   * Menghapus notifikasi kustom berdasarkan ID-nya.
   * @param {string} alertId ID dari alert yang akan dihapus.
   */
  function removeCustomAlert(alertId) {
    if (!alertId) return;
    const alertEl = document.getElementById(alertId);
    if (alertEl) {
      bootstrap.Alert.getOrCreateInstance(alertEl)?.close();
    }
  }
  // ---------- Tetap pertahankan fungsi2 yang sudah ada ----------
  function setStatus(s){
    const field   = document.getElementById('statusField');
    const preview = document.getElementById('statusPreview');
    const alasan  = document.getElementById('alasanInput');
    const label   = document.getElementById('alasanLabel');
    const fileContainer = document.getElementById('fileUploadContainer');
    const fileInput = document.getElementById('fileInput');
    const fileLabel = document.getElementById('fileLabel');
    const terlambatInfo = document.getElementById('terlambatInfo');

    field.value = s;
    preview.value = s;

    // Sembunyikan dan reset input file secara default
    fileContainer.style.display = 'none';
    fileInput.removeAttribute('required');

    // Sesuaikan tampilan kolom alasan berdasarkan status
    if (s === 'Hadir') {
      alasan.removeAttribute('required');
      alasan.style.display = 'none';
      label.textContent = '';
      if (terlambatInfo) terlambatInfo.classList.add('d-none');
    } else if (s === 'Terlambat') {
      alasan.setAttribute('required', 'required');
      alasan.style.display = 'block';
      label.textContent = 'Alasan (wajib untuk terlambat)';
      alasan.placeholder = 'Contoh: macet, ban bocor, antar anak, dsb.';
      if (terlambatInfo) terlambatInfo.classList.remove('d-none');
    } else if (s === 'Izin') {
      alasan.setAttribute('required', 'required');
      alasan.style.display = 'block';
      label.textContent = 'Alasan';
      alasan.placeholder = 'Isi alasan untuk izin';
      if (terlambatInfo) terlambatInfo.classList.add('d-none');
    } else if (s === 'Tugas Luar' || s === 'Cuti') {
      alasan.removeAttribute('required');
      alasan.style.display = 'block';
      label.textContent = 'Keterangan (opsional)';
      alasan.placeholder = 'Contoh: Mengikuti rapat di...';
      
      // Tampilkan input file dan jadikan wajib
      fileContainer.style.display = 'block';
      fileInput.setAttribute('required', 'required');
      fileLabel.textContent = (s === 'Tugas Luar') 
        ? 'Upload Surat Tugas (wajib)' 
        : 'Upload Surat Cuti (wajib)';
    } else {
      alasan.removeAttribute('required');
      alasan.style.display = 'block';
      label.textContent = 'Keterangan (opsional)';
      alasan.placeholder = 'Masukkan keterangan';
      if (terlambatInfo) terlambatInfo.classList.add('d-none');
    }
  }

  function lockSubmit(form){
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.querySelector('.btn-text').classList.add('d-none');
    btn.querySelector('.spinner-border').classList.remove('d-none');
    return true;
  }

  // ---------- Variabel lokasi & util ----------
  const officeLat    = {{ $office['lat'] }};
  const officeLng    = {{ $office['lng'] }};
  const officeRadius = {{ $office['radius'] }}; // meter

  // global state: apakah user saat ini dianggap di dalam radius kantor
  let insideOffice = false;
  let lastDist = null; // simpan jarak terakhir yang dihitung

  function getDistance(lat1, lon1, lat2, lon2) {
    const R = 6371000;
    const toRad = d => d * Math.PI / 180;
    const dLat = toRad(lat2 - lat1);
    const dLon = toRad(lon2 - lon1);
    const a = Math.sin(dLat/2)**2 +
              Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
              Math.sin(dLon/2)**2;
    return 2 * R * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
  }

  // (Tetap sediakan fungsi enable/disable — dipakai jika backend menginginkan)
  function enableHadir() {
    const btn = document.getElementById('btnHadir');
    if (!btn) return;
    btn.classList.remove('disabled');
    btn.style.pointerEvents = 'auto';
    btn.style.opacity = 1;
  }
  function disableHadir() {
    const btn = document.getElementById('btnHadir');
    if (!btn) return;
    btn.classList.add('disabled');
    btn.style.pointerEvents = 'none';
    btn.style.opacity = .5;
  }

  // Update panel info lokasi yang user-friendly
  function updateLocationInfo(statusText, distance, accuracy) {
    const statusEl = document.getElementById('geo-status');
    const distanceEl = document.getElementById('geo-distance');
    const accuracyEl = document.getElementById('geo-accuracy');

    if (statusEl) {
      statusEl.textContent = statusText;
      statusEl.className = `fw-bold fs-5 ${statusText === 'Di Dalam Kantor' ? 'text-success' : 'text-danger'}`;
    }
    if (distanceEl) distanceEl.textContent = distance !== null ? `~ ${Math.round(distance)} m` : '-';
    if (accuracyEl) accuracyEl.textContent = accuracy !== null ? `± ${Math.round(accuracy)} m` : '-';
  }

  // ---------- Perubahan penting: decide() hanya update state, tidak disable permanently ----------
  function decide(lat, lng, acc) {
    const dist = getDistance(lat, lng, officeLat, officeLng);
    lastDist = dist;

    // update state: apakah user dalam radius (dengan toleransi akurasi)
    insideOffice = (dist <= officeRadius + acc);
    
    // Update UI
    const statusText = insideOffice ? 'Di Dalam Kantor' : 'Di Luar Kantor';
    updateLocationInfo(statusText, dist, acc);
  }

  function handlePos(pos) {
    const lat = pos.coords.latitude;
    const lng = pos.coords.longitude;
    const acc = pos.coords.accuracy;
    decide(lat, lng, acc);
  }

  function handleErr(err) {
    console.warn('Geolocation error:', err);
    // jangan disableHadir() permanen — cukup set state false
    insideOffice = false;
    updateLocationInfo('Gagal', null, null);
    // optionally inform user
    // showCustomAlert('Tidak bisa mengambil lokasi: ' + err.message);
  }

  // start sekali untuk update status lokasi (jika browser mendukung)
  if (navigator.geolocation) {
    const opts = { enableHighAccuracy: true, timeout: 20000, maximumAge: 0 };
    navigator.geolocation.getCurrentPosition(handlePos, handleErr, opts);
    const watchId = navigator.geolocation.watchPosition(handlePos, handleErr, opts);
    // hentikan watch setelah 30s agar tidak boros baterai
    setTimeout(() => navigator.geolocation.clearWatch(watchId), 30000);
  } else {
    // browser tidak mendukung: biarkan insideOffice=false (tombol tetap bisa diklik,
    // namun nanti saat modal show akan dicek dan ditolak jika butuh lokasi)
    insideOffice = false;
    updateLocationInfo('Tidak Didukung', null, null);
    // showCustomAlert('Browser tidak mendukung geolocation.');
  }

  // ---------- Intersep show modal (Bootstrap) dan batalkan bila perlu ----------
  document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('absenModal');
    if (!modalEl) return;

    // saat modal akan ditampilkan, event 'show.bs.modal' memberikan relatedTarget
    modalEl.addEventListener('show.bs.modal', function (e) {
      // Ambil status dari field (setStatus biasanya dipanggil saat klik)
      let status = null;
      const statusField = document.getElementById('statusField');
      if (statusField) status = statusField.value;

      // jika belum ada, cobalah ambil dari trigger (relatedTarget)
      if (!status && e && e.relatedTarget) {
        const trg = e.relatedTarget;
        // coba cari text di <h6> atau textContent tombol
        const h6 = trg.querySelector && trg.querySelector('h6');
        status = (h6 && h6.textContent) ? h6.textContent.trim() : (trg.textContent || '').trim();
      }

      // Hanya lakukan pengecekan lokasi untuk HADIR & TERLAMBAT
      if (status === 'Hadir' || status === 'Terlambat') {
        // LOGIKA BARU: Langsung percaya pada status 'insideOffice' yang sudah di-update secara real-time.
        // Tidak perlu getCurrentPosition() lagi, untuk membuat proses lebih cepat.
        if (!insideOffice) {
          // Batalkan pembukaan modal
          e.preventDefault();
          
          // Tampilkan notifikasi berdasarkan informasi yang sudah ada.
          if (lastDist !== null) {
            showCustomAlert('Anda berada di luar area kantor (jarak ~' + Math.round(lastDist) + ' m).');
          } else {
            showCustomAlert('Lokasi Anda belum terdeteksi atau berada di luar jangkauan.');
          }
        }
        // else -> insideOffice=true sehingga modal akan ditampilkan normal
      }
      // Untuk status selain Hadir/Terlambat, modal tetap akan muncul (tidak dicegah)
    });
  });
</script>
@endpush
@endsection

@push('scripts')
<script>
  // Dapatkan tanggal hari ini (sesuai zona waktu browser pengguna) dengan format YYYY-MM-DD
  const today = new Date().toLocaleDateString('en-CA'); // Format 'en-CA' menghasilkan 'YYYY-MM-DD'
  const rekapRef = database.ref('rekap/' + today);

  // Listener utama: akan berjalan sekali saat halaman dimuat,
  // dan akan berjalan lagi setiap kali data di path 'rekap/YYYY-MM-DD' berubah.
  rekapRef.on('value', (snapshot) => {
    const data = snapshot.val();
    console.log("Menerima data rekap terbaru dari Firebase:", data); // Untuk debugging
    updateRekapTable(data);
  });

  function updateRekapTable(rekapData) {
    if (!rekapData) { // Jika belum ada data rekap di Firebase untuk hari ini, jangan lakukan apa-apa
      console.log("Belum ada data rekap di Firebase untuk hari ini.");
      return;
    }

    // Cari elemen tabel di dalam DOM
    const tableBody = document.querySelector('.table-responsive tbody');
    const tableFoot = document.querySelector('.table-responsive tfoot');

    // Reset variabel total
    let totalHadir = 0, totalCuti = 0, totalSakit = 0;
    let totalTugasLuar = 0, totalTerlambat = 0, totalIzin = 0;

    // Iterasi setiap baris <tr> di dalam <tbody>
    tableBody.querySelectorAll('tr').forEach(row => {
      const bidangName = row.cells[0].textContent.trim();
      const rekapBidang = rekapData[bidangName] || {}; // Ambil data untuk bidang ini, atau object kosong jika tidak ada

      // Update setiap cell <td>. Gunakan '?? 0' untuk default ke 0 jika datanya null.
      row.cells[2].textContent = rekapBidang.hadir ?? 0;
      row.cells[3].textContent = rekapBidang.cuti ?? 0;
      row.cells[4].textContent = rekapBidang.sakit ?? 0;
      row.cells[5].textContent = rekapBidang.tugas_luar ?? 0;
      row.cells[6].textContent = rekapBidang.terlambat ?? 0;
      row.cells[7].textContent = rekapBidang.izin ?? 0;

      // Kalkulasi total untuk footer
      totalHadir     += parseInt(rekapBidang.hadir ?? 0);
      totalCuti      += parseInt(rekapBidang.cuti ?? 0);
      totalSakit     += parseInt(rekapBidang.sakit ?? 0);
      totalTugasLuar += parseInt(rekapBidang.tugas_luar ?? 0);
      totalTerlambat += parseInt(rekapBidang.terlambat ?? 0);
      totalIzin      += parseInt(rekapBidang.izin ?? 0);
    });

    // Update baris total di <tfoot>
    const footerRow = tableFoot.querySelector('tr');
    if(footerRow) {
        footerRow.cells[2].textContent = totalHadir;
        footerRow.cells[3].textContent = totalCuti;
        footerRow.cells[4].textContent = totalSakit;
        footerRow.cells[5].textContent = totalTugasLuar;
        footerRow.cells[6].textContent = totalTerlambat;
        footerRow.cells[7].textContent = totalIzin;
    }
  }
</script>
@endpush
