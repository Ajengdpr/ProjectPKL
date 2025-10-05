@extends('layouts.app')
@section('title', 'Dashboard')
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

    {{-- Hero --}}
    <div class="text-center my-3">
      <img src="{{ asset('img/gedung.png') }}" alt="gedung" class="img-fluid" style="max-height:220px">
      <div class="mt-2">
        <span class="badge rounded-pill text-bg-primary px-3 py-2">
          POINT: <strong>{{ $user->point ?? 0 }}</strong>
        </span>
      </div>
    </div>

    @php
      $locked = ($hadirDisabled ?? false) || ($sudahAbsenToday ?? false);
      $terlambatDisabled = $terlambatDisabled ?? (now('Asia/Makassar')->format('H:i') > '16:00');
    @endphp

    {{-- Tiles --}}
    <div class="row g-3">
      <div class="col-12 col-md-4">
        <a id="btnHadir"
          class="tile cyan w-100 text-decoration-none {{ $locked ? 'disabled' : '' }}"
          style="{{ $locked ? 'pointer-events:none;opacity:.5' : '' }}"
          @unless($locked)
            data-bs-toggle="modal" data-bs-target="#absenModal" onclick="setStatus('Hadir')"
          @endunless
        >
          <i class="bi bi-person"></i><h6>Hadir</h6>
        </a>
      </div>
      <div class="col-12 col-md-4">
        <a class="tile dark w-100 text-decoration-none" data-bs-toggle="modal" data-bs-target="#absenModal" onclick="setStatus('Izin')">
          <i class="bi bi-phone"></i><h6>Izin</h6>
        </a>
      </div>
      <div class="col-12 col-md-4">
        <a class="tile gray w-100 text-decoration-none" data-bs-toggle="modal" data-bs-target="#absenModal" onclick="setStatus('Sakit')">
          <i class="bi bi-emoji-frown"></i><h6>Sakit</h6>
        </a>
      </div>
      <div class="col-12 col-md-4">
        <a class="tile green w-100 text-decoration-none" data-bs-toggle="modal" data-bs-target="#absenModal" onclick="setStatus('Tugas Luar')">
          <i class="bi bi-airplane"></i><h6>Tugas Luar</h6>
        </a>
      </div>
      <div class="col-12 col-md-4">
        <a class="tile yellow w-100 text-decoration-none" data-bs-toggle="modal" data-bs-target="#absenModal" onclick="setStatus('Cuti')">
          <i class="bi bi-x-circle"></i><h6>Cuti</h6>
        </a>
      </div>
      <div class="col-12 col-md-4">
        <a class="tile red w-100 text-decoration-none {{ $terlambatDisabled ? 'disabled' : '' }}"
           data-bs-toggle="modal" data-bs-target="#absenModal"
           onclick="setStatus('Terlambat')">
          <i class="bi bi-alarm"></i><h6>Terlambat</h6>
          @if($terlambatDisabled) <small class="text-danger">Sudah lewat jam 16:00</small> @endif
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

  {{-- Bottom Nav --}}
  <nav class="bottom-nav mt-4">
    <div class="container">
      <ul class="nav justify-content-around py-2">
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <i class="bi bi-house-door me-1"></i> Home
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('statistik') ? 'active' : '' }}" href="{{ route('statistik') }}">
            <i class="bi bi-graph-up me-1"></i> Statistik
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('account') ? 'active' : '' }}" href="{{ route('account') }}">
            <i class="bi bi-person me-1"></i> Account
          </a>
        </li>
      </ul>
    </div>
  </nav>

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
              <input class="form-control" id="alasanInput" name="alasan" placeholder="Tulis alasan bila diperlukan" style="display: none;">
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
  // --- Konfigurasi lokasi kantor ---
  const officeLat    = {{ $office['lat'] }};
  const officeLng    = {{ $office['lng'] }};
  const officeRadius = {{ $office['radius'] }}; // meter

  // --- Fungsi jarak Haversine ---
  function getDistance(lat1, lon1, lat2, lon2) {
    const R = 6371000, toRad = d => d * Math.PI / 180;
    const dLat = toRad(lat2 - lat1), dLon = toRad(lon2 - lon1);
    const a = Math.sin(dLat/2)**2 + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon/2)**2;
    return 2 * R * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
  }

  // --- Enable/disable tombol Hadir & Terlambat ---
  function setBtnState(id, enable) {
    const btn = document.getElementById(id);
    if (!btn) return;
    btn.classList.toggle('disabled', !enable);
    btn.style.pointerEvents = enable ? 'auto' : 'none';
    btn.style.opacity = enable ? 1 : .5;
  }

  // --- Cek lokasi user ---
  function decide(lat, lng, acc) {
    const dist = getDistance(lat, lng, officeLat, officeLng);
    const inArea = dist <= officeRadius + acc;
    setBtnState('btnHadir', inArea);
    setBtnState('btnTerlambat', inArea);
    window.absenLokasiValid = inArea;
  }

  function handlePos(pos) {
    decide(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy);
  }
  function handleErr() {
    setBtnState('btnHadir', false);
    setBtnState('btnTerlambat', false);
    window.absenLokasiValid = false;
  }

  // --- Geolocation watcher ---
  if (navigator.geolocation) {
    const opts = { enableHighAccuracy: true, timeout: 20000, maximumAge: 0 };
    navigator.geolocation.getCurrentPosition(handlePos, handleErr, opts);
    const watchId = navigator.geolocation.watchPosition(handlePos, handleErr, opts);
    setTimeout(() => navigator.geolocation.clearWatch(watchId), 30000);
  } else {
    handleErr();
  }

  // --- Validasi sebelum buka modal absen ---
  function setStatus(s){
    if ((s === 'Hadir' || s === 'Terlambat') && window.absenLokasiValid === false) {
      alert('Anda berada di luar area kantor. Tidak dapat absen ' + s + '.');
      return false;
    }
    document.getElementById('statusField').value = s;
    document.getElementById('statusPreview').value = s;
    // Sederhanakan pengaturan alasan
    const alasan = document.getElementById('alasanInput');
    alasan.style.display = (s === 'Hadir' || s === 'Cuti') ? 'none' : 'block';
    alasan.required = (s === 'Terlambat' || s === 'Izin');
    document.getElementById('alasanLabel').textContent = (s === 'Terlambat') ? 'Alasan (wajib untuk terlambat)' : (s === 'Izin' ? 'Alasan' : '');
  }

  // --- Lock submit tombol ---
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