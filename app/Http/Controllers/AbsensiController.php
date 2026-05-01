<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\User;
use App\Models\Setting; // Ditambahkan
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Contract\Database;
use App\Notifications\AbsenceReported;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;


class AbsensiController extends Controller
{
    private const ALLOWED_STATUSES = [
        'Hadir', 'Izin', 'Cuti', 'Sakit', 'Terlambat', 'Tugas Luar',
    ];

    protected $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function index(Request $request)
    {
        $user     = $request->user();
        $tz       = 'Asia/Makassar'; // Zona waktu
        $today    = now($tz)->toDateString(); // Waktu berdasarkan zona waktu
        $tanggal  = $request->input('tanggal', $today);

        $log = DB::table('absensi')
            ->where('user_id', $user->id)
            ->orderByDesc('tanggal')
            ->orderByDesc('jam')
            ->limit(50)
            ->get();

        $rekapUser = DB::table('absensi')
            ->selectRaw('status, COUNT(*) AS total')
            ->where('user_id', $user->id)
            ->groupBy('status')
            ->pluck('total', 'status');

        $daftarBidang = DB::table('users as u')
            ->select('u.bidang', DB::raw('COUNT(*) AS jumlah_pegawai'))
            ->whereNotNull('u.bidang')
            ->groupBy('u.bidang')
            ->orderBy('u.bidang')
            ->get();

        $rekapPerBidang = collect($this->updateFirebaseRekap($tanggal));

        // ==========================================================
        // PERUBAHAN LOKASI: Mengambil data dari Settings
        // ==========================================================
        $lokasiConfig = Setting::get('lokasi', [
            'lat'    => -3.489179,
            'lng'    => 114.828158,
            'radius' => 100,
        ]);

        $office = [
            'lat'    => (float) $lokasiConfig['lat'],
            'lng'    => (float) $lokasiConfig['lng'],
            'radius' => (int)   $lokasiConfig['radius'],
        ];

        $existingRecord = Absensi::where('user_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        // Absen dianggap "selesai/terkunci" jika sudah approved, ditolak, ATAU jika sudah mengajukan (bukan auto-alpha)
        $sudahAbsenToday = $existingRecord && ($existingRecord->is_approved || $existingRecord->is_rejected || $existingRecord->status !== 'alpha');
        
        // Flag untuk pesan khusus
        $isPending  = $existingRecord && !$existingRecord->is_approved && !$existingRecord->is_rejected && $existingRecord->status !== 'alpha';
        $isRejected = $existingRecord && $existingRecord->is_rejected;

        $lastToday = $existingRecord; 
            
        $jamConfig = array_merge([
            'buka' => '07:00:00',
            'batas_hadir' => '08:00:00',
            'batas_akhir' => '16:00:00',
        ], Setting::get('jam', []));

        $statusConfig = Setting::get('status', [
            'reason' => 'Hari Libur Nasional / Kantor Tutup',
            'hari_libur' => '',
        ]);

        $disableReason   = $statusConfig['reason'] ?? 'Hari Libur Nasional / Kantor Tutup';
        $isAbsensiActive = true;
        
        // Cek jika hari ini ada dalam daftar tanggal libur
        $hariLibur = explode("\n", str_replace("\r", "", $statusConfig['hari_libur'] ?? ''));
        if (in_array(now('Asia/Makassar')->toDateString(), $hariLibur)) {
            $isAbsensiActive = false;
        }

        $currentTime = now('Asia/Makassar')->format('H:i:s');
        $isBeforeBuka = $currentTime < $jamConfig['buka'];
        $isPastBatasHadir = $currentTime > $jamConfig['batas_hadir'];
        $isPastBatasAkhir = $currentTime > $jamConfig['batas_akhir'];

        // Flag untuk UI
        $hadirDisabled = $isBeforeBuka || $isPastBatasHadir || !$isAbsensiActive;
        $akhirExpired = $isPastBatasAkhir || !$isAbsensiActive;
        
        // Flag pembantu untuk pesan pop-up yang spesifik
        $isBeforeBatasHadir = $currentTime <= $jamConfig['batas_hadir'];

        // =================================================================
        // KALKULASI POIN BULANAN LIVE UNTUK DASHBOARD (LOGIKA BARU)
        // =================================================================
        $poinConfig = Setting::get('poin', [
            'hadir'      => 1, 'terlambat'  => 0, 'izin'       => 0,
            'sakit'      => 0, 'cuti'       => 0, 'tugas_luar' => 0, 'alpha' => -1
        ]);
        $cutoffTime = config('absensi.cutoff', '16:00:00');

        $bulan = now($tz)->format('Y-m');
        $absensiBulan = Absensi::where('user_id', $user->id)
            ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulan])
            ->where('is_approved', true) // Hanya yang disetujui
            ->get();

        $carbonBulan = now($tz)->startOfMonth();
        $maxHari = $carbonBulan->isSameMonth(now($tz)) ? now($tz)->day : $carbonBulan->daysInMonth;
        
        $totalPoinBulanan = 0;
        $poinKeyMap = [
            'Hadir'       => 'hadir', 'Terlambat'   => 'terlambat', 'Izin'        => 'izin',
            'Sakit'       => 'sakit', 'Cuti'        => 'cuti', 'Tugas Luar'  => 'tugas_luar', 'alpha' => 'alpha',
        ];

        for ($i = 1; $i <= $maxHari; $i++) {
            $tanggalLoop = $carbonBulan->copy()->day($i);
            $tanggalLoopString = $tanggalLoop->toDateString();

            // JANGAN HITUNG ALPHA JIKA: Weekend ATAU Hari Libur yang diatur Admin
            if ($tanggalLoop->isWeekend() || in_array($tanggalLoopString, $hariLibur)) {
                continue;
            }

            $absen = $absensiBulan->first(fn($item) => Carbon::parse($item->tanggal)->isSameDay($tanggalLoop));

            if ($absen) {
                if (!Carbon::parse($absen->tanggal)->isWeekend()) {
                    $status = $absen->status;
                    $key = $poinKeyMap[$status] ?? null;
                    if ($key && isset($poinConfig[$key])) {
                        if ($status === 'Terlambat' && empty(trim($absen->alasan ?? ''))) {
                            $totalPoinBulanan += (int)($poinConfig['alpha'] ?? 0);
                        } else {
                            $totalPoinBulanan += (int)($poinConfig[$key] ?? 0);
                        }
                    }
                }
            } else {
               // Logika baru untuk menghitung alpha
                $isWeekend = $tanggalLoop->isWeekend();
                
                // Bila belum absen setelah jam absensi dibuka selesai (batas_hadir) maka tanpa keterangan
                // Kita gunakan jamConfig['batas_hadir'] sebagai pemicu Alpha live
                $isTodayBeforeAlpha = $tanggalLoop->isToday() && (now($tz)->format('H:i:s') <= $jamConfig['batas_hadir']);

                // Tambahkan poin alpha HANYA jika BUKAN weekend DAN BUKAN hari ini sebelum jam batas_hadir
                if (!$isWeekend && !$isTodayBeforeAlpha) {
                    $totalPoinBulanan += (int)($poinConfig['alpha'] ?? 0);
                }
            }
        }

        // Timpa poin user dengan hasil perhitungan live bulanan
        $user->point = $totalPoinBulanan;


        return view('dashboard', [
            'user'             => $user,
            'log'              => $log,
            'tanggal'          => $tanggal,
            'daftarBidang'     => $daftarBidang,
            'rekapPerBidang'   => $rekapPerBidang,
            'rekap'            => $rekapUser,
            'office'           => $office,
            'sudahAbsenToday'  => $sudahAbsenToday,
            'isPending'        => $isPending,
            'isRejected'       => $isRejected,
            'lastToday'        => $lastToday,
            'hadirDisabled'    => $hadirDisabled,
            'akhirExpired'     => $akhirExpired,
            'isBeforeBuka'     => $isBeforeBuka,
            'isPastBatasHadir' => $isPastBatasHadir,
            'isBeforeBatasHadir' => $isBeforeBatasHadir,
            'isPastBatasAkhir' => $isPastBatasAkhir,
            'poinConfig'       => $poinConfig,
            'isAbsensiActive'  => $isAbsensiActive,
            'disableReason'    => $disableReason,
            'jamConfig'        => $jamConfig,
        ]);
    }


    public function create(string $status)
    {
        $statusConfig = Setting::get('status', ['reason' => 'Hari Libur', 'hari_libur' => '']);
        $isAbsensiActive = true;
        
        $hariLibur = explode("\n", str_replace("\r", "", $statusConfig['hari_libur'] ?? ''));
        if (in_array(now('Asia/Makassar')->toDateString(), $hariLibur)) {
            $isAbsensiActive = false;
        }

        if (!$isAbsensiActive) {
            $reason = $statusConfig['reason'] ?? 'Hari Libur Nasional / Kantor Tutup';
            return redirect()->route('dashboard')->with('err', "Absensi Ditutup: {$reason}");
        }

        // Konversi status dari URL (e.g., 'tugas-luar') menjadi format yang benar ('Tugas Luar')
        $preset = \Illuminate\Support\Str::title(str_replace('-', ' ', $status));

        // Validasi sederhana, pastikan status yang di-pass valid
        if (!in_array($preset, self::ALLOWED_STATUSES, true)) {
            return redirect()->route('dashboard')->with('err', 'Status absensi tidak valid.');
        }

        // Batas waktu
        $jamConfig = array_merge([
            'buka' => '07:00:00',
            'batas_hadir' => '08:00:00',
            'batas_akhir' => '16:00:00'
        ], Setting::get('jam', []));
        $currentTime = now('Asia/Makassar')->format('H:i:s');

        if ($currentTime < $jamConfig['buka']) {
            return redirect()->route('dashboard')->with('err', 'Sistem absensi belum dibuka.');
        }

        if ($currentTime > $jamConfig['batas_akhir']) {
            return redirect()->route('dashboard')->with('err', 'Waktu presensi sudah berakhir.');
        }

        if (in_array($preset, ['Hadir', 'Izin', 'Sakit', 'Tugas Luar', 'Cuti']) && $currentTime > $jamConfig['batas_hadir']) {
            return redirect()->route('dashboard')->with('err', 'Di luar batas waktu pengajuan.');
        }

        if ($preset === 'Terlambat' && $currentTime <= $jamConfig['batas_hadir']) {
            return redirect()->route('dashboard')->with('err', 'Belum memasuki waktu terlambat.');
        }

        return view('absensi.form', compact('preset'));
    }


    public function store(Request $request)
    {
        $statusConfig = Setting::get('status', ['reason' => 'Hari Libur', 'hari_libur' => '']);
        $isAbsensiActive = true;
        
        $hariLibur = explode("\n", str_replace("\r", "", $statusConfig['hari_libur'] ?? ''));
        if (in_array(now('Asia/Makassar')->toDateString(), $hariLibur)) {
            $isAbsensiActive = false;
        }

        if (!$isAbsensiActive) {
            $reason = $statusConfig['reason'] ?? 'Hari Libur Nasional / Kantor Tutup';
            return redirect()->route('dashboard')->with('err', "Absensi Ditutup: {$reason}");
        }

        // Cek jika hari ini adalah weekend (Sabtu/Minggu)
        if (now('Asia/Makassar')->isWeekend()) {
            return redirect()->route('dashboard')->with('err', 'Absensi tidak dapat dilakukan pada hari Sabtu atau Minggu.');
        }


        $user  = $request->user();
        $tz    = 'Asia/Makassar';
        $today = now($tz)->toDateString();

        // Validasi
        $data = $request->validate([
            'status' => ['required', 'string'],
            'alasan' => ['nullable', 'string', 'max:255'],
            'berkas' => [
                'nullable',
                'file', // Bisa berupa gambar atau dokumen
                'mimes:jpg,jpeg,png,pdf,doc,docx', // Tipe file yang diizinkan
                'max:2048' // Ukuran maksimum 2MB
            ],
        ]);

        $status = trim($data['status']);
        if (!in_array($status, self::ALLOWED_STATUSES, true)) {
            return back()->withErrors('Status tidak valid.');
        }

        // Cek jika sudah ada absen non-alpha yang disetujui
        $existing = Absensi::where('user_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        if ($existing && $existing->status !== 'alpha' && $existing->is_approved) {
            return redirect()->route('dashboard')->with('err', 'Anda sudah absen hari ini, data tidak bisa diubah.');
        }
        
        // ==========================================================
        // PERUBAHAN BATAS WAKTU: Mengambil data dari Settings
        // ==========================================================
        $jamConfig = array_merge([
            'buka' => '07:00:00',
            'batas_hadir' => '08:00:00',
            'batas_akhir' => '16:00:00'
        ], Setting::get('jam', []));
        $currentTime = now($tz)->format('H:i:s');

        // 1. Cek Batas Buka
        if ($currentTime < $jamConfig['buka']) {
            return redirect()->route('dashboard')->with('err', "Sistem absensi belum dibuka.");
        }

        // 2. Cek Batas Akhir
        if ($currentTime > $jamConfig['batas_akhir']) {
            return redirect()->route('dashboard')->with('err', "Waktu presensi sudah berakhir.");
        }

        // 3. Cek Batas Hadir Reguler (Hadir, Izin, Sakit, TL, Cuti)
        if (in_array($status, ['Hadir', 'Izin', 'Sakit', 'Tugas Luar', 'Cuti']) && $currentTime > $jamConfig['batas_hadir']) {
            return redirect()->route('dashboard')->with('err', "Di luar batas waktu pengajuan.");
        }

        // 4. Cek Terlambat (Harus setelah batas_hadir)
        if ($status === 'Terlambat' && $currentTime <= $jamConfig['batas_hadir']) {
            return redirect()->route('dashboard')->with('err', "Belum memasuki waktu terlambat.");
        }

        // Handle file upload jika ada
        $berkasPath = null;
        if ($request->hasFile('berkas')) {
            $berkasPath = $request->file('berkas')->store('absensi_berkas', 'public');
        }

        // Status Hadir & Terlambat otomatis Approved
        $isApproved = in_array($status, ['Hadir', 'Terlambat']);

        // Jika ada record alpha, kita update saja. Jika tidak, buat baru.
        if ($existing) {
            $absen = $existing;
        } else {
            $absen = new Absensi();
            $absen->user_id = $user->id;
            $absen->tanggal = $today;
        }

        $absen->jam         = now($tz)->format('H:i:s');
        $absen->status      = $status;
        $absen->is_approved = $isApproved;
        $absen->alasan      = $data['alasan'] ?? null;
        $absen->berkas      = $berkasPath;
        $absen->save();

        // Update Poin HANYA jika otomatis approved (Hadir/Terlambat)
        if ($isApproved) {
            $poinConfig = Setting::get('poin', [
                'hadir'      => 1, 'terlambat'  => -3, 'izin'       => 0,
                'sakit'      => 0, 'cuti'       => 0, 'tugas_luar' => 0, 'alpha'      => -5
            ]);
            
            $delta = 0;
            $poinKeyMap = [
                'Hadir' => 'hadir', 'Terlambat' => 'terlambat',
            ];

            $key = $poinKeyMap[$status] ?? null;
            if ($key && isset($poinConfig[$key])) {
                if ($status === 'Terlambat' && empty(trim($data['alasan'] ?? ''))) {
                    $delta = (int) ($poinConfig['alpha'] ?? 0);
                } else {
                    $delta = (int) $poinConfig[$key];
                }
            }
            
            if ($delta !== 0) {
                DB::table('users')->where('id', $user->id)->update(['point' => DB::raw("point + ($delta)")]);
            }
        }

        // Update rekap ke Firebase (biar dashboard live)
        $this->updateFirebaseRekap($today);

        /* ============================
           NOTIFIKASI KE ATASAN
           - Status selain Hadir butuh approval
           ============================ */
        if ($status !== 'Hadir') {
            $targets = collect();
            if (isset(self::KEPALA_BIDANG_USERNAME[$user->bidang])) {
                $kabidUsername = self::KEPALA_BIDANG_USERNAME[$user->bidang];
                if ($user->username !== $kabidUsername && $user->username !== self::PLT_KEPALA_DINAS_USERNAME) {
                    $kepala = User::where('username', $kabidUsername)->first();
                    if ($kepala) $targets->push($kepala);
                }
            }

            $isKabid = in_array($user->username, self::KEPALA_BIDANG_USERNAME);
            if ($isKabid) {
                $plt = User::where('username', self::PLT_KEPALA_DINAS_USERNAME)->first();
                if ($plt) $targets->push($plt);
            }

            $targets->each(function (User $atasan) use ($absen, $user, $status, $data, $tz, $berkasPath) {
                $atasan->notify(new AbsenceReported(
                    attId:  $absen->id,
                    namaPegawai: $user->nama,
                    status: $status,
                    alasan: $data['alasan'] ?? null,
                    waktu: now($tz)->format('Y-m-d H:i'),
                    berkas: $berkasPath
                ));
            });
        }

        $msg = "Absensi {$status} tersimpan.";
        if (!$isApproved) $msg .= " Menunggu persetujuan atasan.";

        return redirect()->route('dashboard')->with('ok', $msg);
    }

    public function approve(Request $request, Absensi $absensi)
    {
        $user = $request->user();
        
        if ($absensi->is_approved) {
            return back()->with('err', 'Absensi sudah disetujui sebelumnya.');
        }

        $absensi->is_approved = true;
        $absensi->is_rejected = false;
        $absensi->save();

        // Update Poin setelah disetujui
        $poinConfig = Setting::get('poin', [
            'hadir'      => 1, 'terlambat'  => -3, 'izin'       => 0,
            'sakit'      => 0, 'cuti'       => 0, 'tugas_luar' => 0, 'alpha'      => -5
        ]);

        $poinKeyMap = [
            'Hadir'      => 'hadir', 'Terlambat'  => 'terlambat', 'Izin'       => 'izin',
            'Sakit'      => 'sakit', 'Cuti'       => 'cuti', 'Tugas Luar' => 'tugas_luar',
        ];

        $key = $poinKeyMap[$absensi->status] ?? null;
        if ($key && isset($poinConfig[$key])) {
            $delta = (int) $poinConfig[$key];
            if ($delta !== 0) {
                DB::table('users')->where('id', $absensi->user_id)->update(['point' => DB::raw("point + ($delta)")]);
            }
        }

        $this->updateFirebaseRekap($absensi->tanggal);

        return back()->with('ok', 'Absensi berhasil disetujui.');
    }

    public function reject(Request $request, Absensi $absensi)
    {
        if ($absensi->is_approved) {
            return back()->with('err', 'Absensi yang sudah disetujui tidak bisa ditolak.');
        }

        $absensi->is_rejected = true;
        $absensi->is_approved = false;
        $absensi->save();

        $this->updateFirebaseRekap($absensi->tanggal);

        return back()->with('ok', 'Absensi berhasil ditolak.');
    }
    private function updateFirebaseRekap(string $tanggal): array
    {
        // Jangan hitung rekap untuk hari libur
        if (\Carbon\Carbon::parse($tanggal)->isWeekend()) {
            return [];
        }

        $usersByBidang = User::whereNotNull('bidang')->where('bidang', '!=', '')->get()->groupBy('bidang');
        $absensiHariIni = Absensi::whereDate('tanggal', $tanggal)->get();
        $rekapData = [];

        foreach ($usersByBidang as $namaBidang => $usersInBidang) {
            $userIdsInBidang = $usersInBidang->pluck('id');
            $absensiInBidang = $absensiHariIni->whereIn('user_id', $userIdsInBidang);
            
            // Hanya hitung yang SUDAH DISETUJUI dan BUKAN status alpha
            $approvedNonAlpha = $absensiInBidang->where('is_approved', true)->where('status', '!=', 'alpha');
            $stats = $approvedNonAlpha->countBy(fn($item) => strtolower(trim($item->status)));

            $h = $stats->get('hadir', 0);
            $t = $stats->get('terlambat', 0);
            $i = $stats->get('izin', 0);
            $s = $stats->get('sakit', 0);
            $c = $stats->get('cuti', 0);
            $tl = $stats->get('tugas luar', 0);

            // Alpha (Tanpa Keterangan) = Total Pegawai - Pegawai dengan absen yang disetujui
            // Ini otomatis mencakup: yang belum absen, yang masih pending, dan yang ditolak.
            $userIdsWithApprovedRecord = $approvedNonAlpha->pluck('user_id')->unique();
            $a = $userIdsInBidang->diff($userIdsWithApprovedRecord)->count();

            $rekapData[$namaBidang] = (object)[
                'hadir' => $h,
                'terlambat' => $t,
                'izin' => $i,
                'sakit' => $s,
                'cuti' => $c,
                'tugas_luar' => $tl,
                'alpha' => $a,
            ];
        }

        $firebasePath = 'rekap/' . $tanggal;

        try {
            $this->database->getReference($firebasePath)->set($rekapData);
        } catch (\Exception $e) {
            \Log::error('Firebase update failed: ' . $e->getMessage());
        }

        return $rekapData;
    }

    public function statistik(Request $request)
    {
        $user  = $request->user();
        $bulan = $request->input('bulan', now()->format('Y-m'));
        $tz    = config('app.timezone', 'Asia/Makassar');

        // --- KONSTANTA AKSES (Sesuai sistem Anda) ---
        $kepalaBidangMap = [
            'SEKRETARIAT' => 'noorekahasni',
            'PPKLH'       => 'emmyariani',
            'KPPI'        => 'hajiehariyanie',
            'TALING'      => 'adhimaulana',
            'PHL'         => 'hardiniwijayanti',
        ];
        $pltUsername = 'fathimatuzzahra';

        $isPlt = ($user->username === $pltUsername);
        $bidangLed = array_search($user->username, $kepalaBidangMap);
        $isAtasan = ($isPlt || $bidangLed);
        // --------------------------------------------

        // Ambil pengaturan poin & hari libur
        $poinConfig = Setting::get('poin', ['hadir'=>1, 'terlambat'=>0, 'izin'=>0, 'sakit'=>0, 'cuti'=>0, 'tugas_luar'=>0, 'alpha'=>-1]);
        $statusConfig = Setting::get('status', ['hari_libur' => '']);
        $hariLibur = explode("\n", str_replace("\r", "", $statusConfig['hari_libur'] ?? ''));
        $cutoffTime = config('absensi.cutoff', '16:00:00');
        $poinKeyMap = ['Hadir'=>'hadir', 'Terlambat'=>'terlambat', 'Izin'=>'izin', 'Sakit'=>'sakit', 'Cuti'=>'cuti', 'Tugas Luar'=>'tugas_luar', 'alpha'=>'alpha'];

        // Tentukan rentang hari
        $carbonBulan = Carbon::parse($bulan.'-01', $tz);
        $maxHari = $carbonBulan->daysInMonth;
        if ($carbonBulan->isFuture()) {
            $maxHari = 0;
        } elseif ($carbonBulan->isSameMonth(now($tz))) {
            $maxHari = now($tz)->day;
        }

        // Ambil semua absensi pada bulan terpilih untuk efisiensi
        $allAbsensiBulan = Absensi::whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulan])
            ->where('is_approved', true) // Hanya yang disetujui
            ->get()->groupBy('user_id');

        // =================================================================
        // 2. DATA RANKING (RANKING POIN)
        // =================================================================
        $allUsers = User::where('role', '!=', 'admin')->get();
        $monthlyScores = [];

        // Ambil pengaturan hari libur untuk mengabaikan poin alpha di hari tersebut
        $statusConfig = Setting::get('status', ['hari_libur' => '']);
        $hariLibur = explode("\n", str_replace("\r", "", $statusConfig['hari_libur'] ?? ''));

        // Kita perlu jamConfig untuk batas Alpha
        $jamConfig = array_merge(['batas_hadir' => '08:00:00'], Setting::get('jam', []));

        foreach ($allUsers as $u) {
            $uAbs = $allAbsBulan->get($u->id, collect());
            $uPoin = 0;
            for ($i = 1; $i <= $maxHari; $i++) {
                $tL = $carbonBulan->copy()->day($i);
                if ($tL->isWeekend() || in_array($tL->toDateString(), $hariLibur)) continue;
                $a = $uAbs->first(fn($item) => Carbon::parse($item->tanggal)->isSameDay($tL));
                if($a){
                    $k = $poinKeyMap[$a->status] ?? null;
                    $uPoin += ($a->status === 'Terlambat' && empty(trim($a->alasan ?? ''))) ? (int)($poinConfig['alpha'] ?? 0) : (int)($poinConfig[$k] ?? 0);
                } else {
                    // LOGIKA BARU YANG BENAR:
                    // Hitung alpha jika hari sudah lewat, ATAU jika hari ini & sudah lewat jam batas_hadir
                    $isWeekend = $tanggalLoop->isWeekend();
                    $isTodayBeforeAlpha = $tanggalLoop->isToday() && (now($tz)->format('H:i:s') <= $jamConfig['batas_hadir']);

                    // Tambahkan poin alpha HANYA jika BUKAN weekend DAN BUKAN hari ini sebelum jam batas_hadir
                    if (!$isWeekend && !$isTodayBeforeAlpha) {
                        $totalPoinLoop += (int)($poinConfig['alpha'] ?? 0);
                    }
                }
            }
            $scores[] = (object)['nama' => $u->nama, 'poin_total' => $uPoin];
        }
        $col = collect($scores);
        $top5Global = $col->sortByDesc('poin_total')->take(5)->values();
        $bottom5Global = $col->sortBy('poin_total')->take(5)->values();

        // =================================================================
        // 3. DATA ANGGOTA BIDANG (UNTUK ATASAN)
        // =================================================================
        $subordinates = collect();
        $targetSub = null;
        $subStats = null;

        if ($isAtasan) {
            $qSub = User::where('role', '!=', 'admin')->where('id', '!=', $user->id)->orderBy('nama');
            if (!$isPlt) $qSub->where('bidang', $bidangLed);
            $subordinates = $qSub->get();

            $subId = $request->input('sub_id');
            if ($subId && $subordinates->contains('id', $subId)) {
                $targetSub = User::find($subId);
                $subAbs = Absensi::where('user_id', $subId)->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulan])->get();
                $sPoin = 0; $sRekap = ['Hadir'=>0, 'Izin'=>0, 'Cuti'=>0, 'Sakit'=>0, 'Terlambat'=>0, 'Tugas Luar'=>0, 'Tanpa Keterangan'=>0];
                for ($i = 1; $i <= $maxHari; $i++) {
                    $tL = $carbonBulan->copy()->day($i);
                    if ($tL->isWeekend() || in_array($tL->toDateString(), $hariLibur)) continue;
                    $a = $subAbs->first(fn($item) => Carbon::parse($item->tanggal)->isSameDay($tL));
                    if($a){
                        $sRekap[$a->status]++;
                        $k = $poinKeyMap[$a->status] ?? null;
                        $sPoin += ($a->status === 'Terlambat' && empty(trim($a->alasan ?? ''))) ? (int)($poinConfig['alpha'] ?? 0) : (int)($poinConfig[$k] ?? 0);
                    } else {
                        $totalPoin += (int)($poinConfig[$key] ?? 0);
                    }
                }
            } else {
                $isWeekend = $tanggalLoop->isWeekend();
                $isTodayBeforeAlpha = $tanggalLoop->isToday() && (now($tz)->format('H:i:s') <= $jamConfig['batas_hadir']);

                // Tambahkan poin alpha HANYA jika BUKAN weekend DAN BUKAN hari ini sebelum jam batas_hadir
                if (!$isWeekend && !$isTodayBeforeAlpha) {
                    $rekapData['Tanpa Keterangan']++;
                    $totalPoin += (int)($poinConfig['alpha'] ?? 0);
                }
            }
        }
        unset($rekapData['alpha']);

        $adaData = array_sum($rekapData) > 0;

        // Definisikan label status untuk dikirim ke view (dibutuhkan oleh chart)
        $statuses = \App\Models\Absensi::getStatuses();
        $statusLabels = array_values($statuses);

        return view('statistik', [
            'user' => $user, 'absensi' => $absensi, 'bulan' => $bulan, 'totalPoin' => $totalPoin, 'rekapData' => $rekapData,
            'top5Global' => $top5Global, 'bottom5Global' => $bottom5Global, 'poinConfig' => $poinConfig,
            'isAtasan' => $isAtasan, 'subordinates' => $subordinates, 'targetSub' => $targetSub, 'subStats' => $subStats,
            'statusColors' => ['Hadir'=>'#36A2EB', 'Izin'=>'#FFCE56', 'Cuti'=>'#9966FF', 'Sakit'=>'#FF6384', 'Terlambat'=>'#4BC0C0', 'Tugas Luar'=>'#FF9F40', 'Tanpa Keterangan'=>'#e0e0e0'],
            'poinKeyMap' => $poinKeyMap, 'adaData' => array_sum($rekapData) > 0
        ]);
    }

    public function exportCsvUser(Request $r): StreamedResponse
    {
        $loggedInUser = $r->user();
        $bulan = $r->input('bulan', now()->format('Y-m'));
        $tz = config('app.timezone', 'Asia/Makassar');

        // Logic to determine which user data to export
        $targetUser = $loggedInUser;
        $requestedUserId = $r->input('user_id');

        if ($requestedUserId && $requestedUserId != $loggedInUser->id) {
            // Check if logged in user is Atasan
            $kepalaBidangMap = [
                'SEKRETARIAT' => 'noorekahasni',
                'PPKLH'       => 'emmyariani',
                'KPPI'        => 'hajiehariyanie',
                'TALING'      => 'adhimaulana',
                'PHL'         => 'hardiniwijayanti',
            ];
            $pltUsername = 'fathimatuzzahra';
            
            $isPlt = ($loggedInUser->username === $pltUsername);
            $bidangLed = array_search($loggedInUser->username, $kepalaBidangMap);
            
            $requestedUser = User::find($requestedUserId);
            if ($requestedUser && ($isPlt || ($bidangLed && $requestedUser->bidang === $bidangLed))) {
                $targetUser = $requestedUser;
            }
        }

        $filename = 'rekap_absensi_' . $targetUser->username . '_' . $bulan . '.csv';

        $carbonBulan = Carbon::parse($bulan . '-01', $tz);
        $maxHari = $carbonBulan->daysInMonth;
        if ($carbonBulan->isFuture()) {
            $maxHari = 0;
        } elseif ($carbonBulan->isSameMonth(now($tz))) {
            $maxHari = now($tz)->day;
        }

        $absensiBulan = Absensi::where('user_id', $targetUser->id)
            ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulan])
            ->where('is_approved', true) // Hanya yang disetujui
            ->get()
            ->keyBy('tanggal');

        $jamConfig = array_merge(['batas_hadir' => '08:00:00'], Setting::get('jam', []));
        $cutoffTime = $jamConfig['batas_hadir'];
        $statuses = Absensi::getStatuses();

        return response()->streamDownload(function () use ($absensiBulan, $carbonBulan, $maxHari, $tz, $cutoffTime, $statuses) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Tanggal', 'Status', 'Jam', 'Alasan']);

            for ($i = 1; $i <= $maxHari; $i++) {
                $tanggalLoop = $carbonBulan->copy()->day($i);
                $tanggalString = $tanggalLoop->toDateString();
                $absen = $absensiBulan->get($tanggalString);

                if ($absen) {
                    $statusText = $statuses[$absen->status] ?? $absen->status;
                    fputcsv($out, [
                        $absen->tanggal,
                        $statusText,
                        $absen->jam,
                        $absen->alasan,
                    ]);
                } else {
                    if ($tanggalLoop->isPast() || ($tanggalLoop->isToday() && now($tz)->format('H:i:s') > $cutoffTime)) {
                        fputcsv($out, [
                            $tanggalString,
                            $statuses['alpha'],
                            '',
                            '',
                        ]);
                    }
                }
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
