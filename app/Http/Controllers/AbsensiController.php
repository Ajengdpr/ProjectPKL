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

        $sudahAbsenToday = DB::table('absensi')
            ->where('user_id', $user->id)
            ->whereDate('tanggal', $today)
            ->exists();

        $lastToday = DB::table('absensi')
            ->where('user_id', $user->id)
            ->whereDate('tanggal', $today)
            ->orderByDesc('jam')
            ->first();
            
        $jamConfig = array_merge([
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

        $hadirDisabled = now('Asia/Makassar')->format('H:i:s') > $jamConfig['batas_hadir'];
        $akhirExpired = now('Asia/Makassar')->format('H:i:s') > $jamConfig['batas_akhir'];

        // Jika sistem dinonaktifkan admin, anggap sudah expired agar tombol tidak bisa diklik
        if (!$isAbsensiActive) {
            $hadirDisabled = true;
            $akhirExpired = true;
        }

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
            ->get();

        $carbonBulan = now($tz)->startOfMonth();
        $maxHari = $carbonBulan->isSameMonth(now($tz)) ? now($tz)->day : $carbonBulan->daysInMonth;
        
        $totalPoinBulanan = 0;
        $poinKeyMap = [
            'Hadir'       => 'hadir', 'Terlambat'   => 'terlambat', 'Izin'        => 'izin',
            'Sakit'       => 'sakit', 'Cuti'        => 'cuti', 'Tugas Luar'  => 'tugas_luar', 'alpha' => 'alpha',
        ];

        foreach ($absensiBulan as $absen) {
            $tanggalAbsen = Carbon::parse($absen->tanggal);
            if (!$tanggalAbsen->isWeekend()) {
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
            'lastToday'        => $lastToday,
            'hadirDisabled'    => $hadirDisabled,
            'akhirExpired'     => $akhirExpired,
            'poinConfig'       => $poinConfig,
            'isAbsensiActive'  => $isAbsensiActive,
            'disableReason'    => $disableReason,
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

        // Cek apakah hari ini sudah absen
        if (Absensi::where('user_id', auth()->id())->whereDate('tanggal', now('Asia/Makassar')->toDateString())->exists()) {
            return redirect()->route('dashboard')->with('err', 'Anda sudah absen hari ini.');
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

        // Cegah double absen per hari
        if (Absensi::where('user_id', $user->id)->whereDate('tanggal', $today)->exists()) {
            return redirect()->route('dashboard')->with('err', 'Anda sudah absen hari ini, data tidak bisa diubah.');
        }
        
        // ==========================================================
        // PERUBAHAN BATAS WAKTU: Mengambil data dari Settings
        // ==========================================================
        $jamConfig = Setting::get('jam', ['batas_hadir' => '08:00:00']);
        $batasHadir = $jamConfig['batas_hadir'];

        if ($status === 'Hadir' && now($tz)->format('H:i:s') > $batasHadir) {
            return redirect()->route('dashboard')->with('err', "Absen Hadir ditutup setelah " . substr($batasHadir, 0, 5) . " WITA.");
        }

        // Handle file upload jika ada
        $berkasPath = null;
        if ($request->hasFile('berkas')) {
            $berkasPath = $request->file('berkas')->store('absensi_berkas', 'public');
        }

        // Simpan absensi beserta device_id
        $absen = new Absensi();
        $absen->user_id = $user->id;
        $absen->tanggal = $today;
        $absen->jam     = now($tz)->format('H:i:s');
        $absen->status  = $status;
        $absen->alasan  = $data['alasan'] ?? null;
        $absen->berkas  = $berkasPath;
        $absen->save();

        // ==========================================================
        // PERUBAHAN POIN: Mengambil data dari Settings
        // ==========================================================
        $poinConfig = Setting::get('poin', [
            'hadir'      => 1,
            'terlambat'  => -3,
            'izin'       => 0,
            'sakit'      => 0,
            'cuti'       => 0,
            'tugas_luar' => 0,
            'alpha'      => -5
        ]);
        
        $delta = 0;

        $poinKeyMap = [
            'Hadir'      => 'hadir',
            'Terlambat'  => 'terlambat',
            'Izin'       => 'izin',
            'Sakit'      => 'sakit',
            'Cuti'       => 'cuti',
            'Tugas Luar' => 'tugas_luar',
        ];

        $key = $poinKeyMap[$status] ?? null;

        if ($key && isset($poinConfig[$key])) {
             // Jika status adalah Terlambat dan tidak ada alasan, gunakan poin 'alpha' (Tanpa Keterangan)
            if ($status === 'Terlambat' && empty(trim($data['alasan'] ?? ''))) {
                 $delta = (int) ($poinConfig['alpha'] ?? 0);
            } else {
                // Untuk status lain atau Terlambat dengan alasan, gunakan poin statusnya
                 $delta = (int) $poinConfig[$key];
            }
        }
        
        if ($delta !== 0) {
            DB::table('users')->where('id', $user->id)->update(['point' => DB::raw("point + ($delta)")]);
        }

        // Update rekap ke Firebase (biar dashboard live)
        $this->updateFirebaseRekap($today);

        return redirect()->route('dashboard')->with('ok', "Absensi {$status} tersimpan.");
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
            $stats = $absensiInBidang->countBy(fn($item) => strtolower(trim($item->status)));

            $h = $stats->get('hadir', 0);
            $t = $stats->get('terlambat', 0);
            $i = $stats->get('izin', 0);
            $s = $stats->get('sakit', 0);
            $c = $stats->get('cuti', 0);
            $tl = $stats->get('tugas luar', 0);

            $alphaFromDb = $stats->get('alpha', 0);
            $sudahAbsenInBidang = $absensiInBidang->pluck('user_id')->unique();
            $alphaFromNoRecord = $userIdsInBidang->diff($sudahAbsenInBidang)->count();
            $a = $alphaFromDb + $alphaFromNoRecord;

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
        $maxHari = $carbonBulan->isFuture() ? 0 : ($carbonBulan->isSameMonth(now($tz)) ? now($tz)->day : $carbonBulan->daysInMonth);

        // =================================================================
        // 1. DATA PRIBADI (UNTUK BAGIAN ATAS)
        // =================================================================
        $absensi = Absensi::where('user_id', $user->id)->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulan])->get();
        $totalPoin = 0; 
        $rekapData = ['Hadir'=>0, 'Izin'=>0, 'Cuti'=>0, 'Sakit'=>0, 'Terlambat'=>0, 'Tugas Luar'=>0, 'Tanpa Keterangan'=>0];

        for ($i = 1; $i <= $maxHari; $i++) {
            $tLoop = $carbonBulan->copy()->day($i);
            if ($tLoop->isWeekend() || in_array($tLoop->toDateString(), $hariLibur)) continue;
            $ab = $absensi->first(fn($item) => Carbon::parse($item->tanggal)->isSameDay($tLoop));
            if ($ab) {
                $rekapData[$ab->status]++;
                $key = $poinKeyMap[$ab->status] ?? null;
                $totalPoin += ($ab->status === 'Terlambat' && empty(trim($ab->alasan ?? ''))) ? (int)($poinConfig['alpha'] ?? 0) : (int)($poinConfig[$key] ?? 0);
            } else {
                if (!($tLoop->isToday() && now($tz)->format('H:i:s') <= $cutoffTime)) {
                    $rekapData['Tanpa Keterangan']++;
                    $totalPoin += (int)($poinConfig['alpha'] ?? 0);
                }
            }
        }

        // =================================================================
        // 2. DATA RANKING (RANKING POIN)
        // =================================================================
        $allUsers = User::where('role', '!=', 'admin')->get();
        $allAbsBulan = Absensi::whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulan])->get()->groupBy('user_id');
        $scores = [];
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
                    if (!($tL->isToday() && now($tz)->format('H:i:s') <= $cutoffTime)) $uPoin += (int)($poinConfig['alpha'] ?? 0);
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
                        if (!($tL->isToday() && now($tz)->format('H:i:s') <= $cutoffTime)) {
                            $sRekap['Tanpa Keterangan']++;
                            $sPoin += (int)($poinConfig['alpha'] ?? 0);
                        }
                    }
                }
                $subStats = ['poin' => $sPoin, 'rekap' => $sRekap, 'absensi' => $subAbs];
            }
        }

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
            ->get()
            ->keyBy('tanggal');

        $cutoffTime = config('absensi.cutoff', '16:00:00');
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
