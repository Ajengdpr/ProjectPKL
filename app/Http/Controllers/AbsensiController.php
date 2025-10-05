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

    private const KEPALA_BIDANG_USERNAME = [
    'SEKRETARIAT' => 'noorekahasni',
    'PPKLH'       => 'emmyariani',
    'KPPI'        => 'hajiehariyanie',
    'TALING'      => 'adhimaulana',
    'PHL'         => 'hardiniwijayanti',
    ];

    private const PLT_KEPALA_DINAS_USERNAME = 'fathimatuzzahra';

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

        $rekapPerBidang = DB::table('users as u')
            ->leftJoin('absensi as a', function ($join) use ($tanggal) {
                $join->on('a.user_id', '=', 'u.id')
                     ->whereDate('a.tanggal', $tanggal);
            })
            ->select(
                'u.bidang',
                DB::raw("SUM(CASE WHEN a.status = 'Hadir'      THEN 1 ELSE 0 END) AS hadir"),
                DB::raw("SUM(CASE WHEN a.status = 'Cuti'       THEN 1 ELSE 0 END) AS cuti"),
                DB::raw("SUM(CASE WHEN a.status = 'Sakit'      THEN 1 ELSE 0 END) AS sakit"),
                DB::raw("SUM(CASE WHEN a.status = 'Tugas Luar' THEN 1 ELSE 0 END) AS tugas_luar"),
                DB::raw("SUM(CASE WHEN a.status = 'Terlambat'  THEN 1 ELSE 0 END) AS terlambat"),
                DB::raw("SUM(CASE WHEN a.status = 'Izin'       THEN 1 ELSE 0 END) AS izin")
            )
            ->groupBy('u.bidang')
            ->get()
            ->keyBy('bidang');

        // ==========================================================
        // PERUBAHAN LOKASI: Mengambil data dari Settings
        // ==========================================================
        $lokasiConfig = Setting::get('lokasi', [
            'lat'    => -3.489179,
            'lng'    => 114.828158,
            'radius' => 200,
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
            
        $jamConfig = Setting::get('jam', ['batas_hadir' => '08:00:00']);
        $hadirDisabled = now('Asia/Makassar')->format('H:i:s') > $jamConfig['batas_hadir'];

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
            'Sakit'       => 'sakit', 'Cuti'        => 'cuti', 'Tugas Luar'  => 'tugas_luar', 'Alpha' => 'alpha',
        ];

        for ($i = 1; $i <= $maxHari; $i++) {
            $tanggalLoop = $carbonBulan->copy()->day($i);
            $absen = $absensiBulan->first(fn($item) => Carbon::parse($item->tanggal)->isSameDay($tanggalLoop));

            if ($absen) {
                $status = $absen->status;
                $key = $poinKeyMap[$status] ?? null;
                if ($key && isset($poinConfig[$key])) {
                    if ($status === 'Terlambat' && empty(trim($absen->alasan ?? ''))) {
                        $totalPoinBulanan += (int)($poinConfig['alpha'] ?? 0);
                    } else {
                        $totalPoinBulanan += (int)($poinConfig[$key] ?? 0);
                    }
                }
            } else {
                if ($tanggalLoop->isPast() || ($tanggalLoop->isToday() && now($tz)->format('H:i:s') > $cutoffTime)) {
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
            'lastToday'        => $lastToday,
            'hadirDisabled'    => $hadirDisabled,
        ]);
    }


    public function store(Request $request)
    {
        $user  = $request->user();
        $tz    = 'Asia/Makassar';
        $today = now($tz)->toDateString();

        // Validasi
        $data = $request->validate([
            'status' => ['required', 'string'],
            'alasan' => ['nullable', 'string', 'max:255'],
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

        $deviceId = $request->input('device_id');
        $today = now('Asia/Makassar')->toDateString();

        // Cek apakah device sudah absen hari ini
        $already = Absensi::where('device_id', $deviceId)
            ->whereDate('tanggal', $today)
            ->exists();

        if($already){
            return back()->withErrors('Device ini sudah melakukan absensi hari ini.');
        }

        // Simpan absensi beserta device_id
        $absen = new Absensi();
        $absen->user_id = $user->id;
        $absen->tanggal = $today;
        $absen->device_id = $deviceId;
        $absen->jam     = now($tz)->format('H:i:s');
        $absen->status  = $status;
        $absen->alasan  = $data['alasan'] ?? null;
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

        /* ============================
           NOTIFIKASI KE ATASAN
           - Hanya untuk status selain Hadir & Cuti (sesuai permintaanmu)
           - Ke kepala bidang sesuai bidang user
           - Juga ke PLT kepala dinas
           ============================ */
        if (!in_array($status, ['Hadir','Cuti'], true)) {
            $targets = collect();

            // Kepala bidang sesuai bidang user
            if (isset(self::KEPALA_BIDANG_USERNAME[$user->bidang])) {
                $kepala = User::where('username', self::KEPALA_BIDANG_USERNAME[$user->bidang])->first();
                if ($kepala) $targets->push($kepala);
            }

            // PLT kepala dinas
            $plt = User::where('username', self::PLT_KEPALA_DINAS_USERNAME)->first();
            if ($plt) $targets->push($plt);

            // Kirim (hindari duplikasi untuk att_id sama)
            $targets->each(function (User $atasan) use ($absen, $user, $status, $data, $tz) {
                $sudahAda = $atasan->notifications()
                    ->where('type', \App\Notifications\AbsenceReported::class)
                    ->where('data->att_id', $absen->id)
                    ->exists();

                if (!$sudahAda) {
                    $atasan->notify(new AbsenceReported(
                        attId:  $absen->id,
                        namaPegawai: $user->nama,
                        status: $status,
                        alasan: $data['alasan'] ?? null,
                        waktu: now($tz)->format('Y-m-d H:i')
                    ));
                }
            });
        }

        return redirect()->route('dashboard')->with('ok', "Absensi {$status} tersimpan.");
    }
    private function updateFirebaseRekap(string $tanggal)
    {
        $rekapData = DB::table('users as u')
            ->leftJoin('absensi as a', function ($join) use ($tanggal) {
                $join->on('a.user_id', '=', 'u.id')
                     ->whereDate('a.tanggal', $tanggal);
            })
            ->select(
                'u.bidang',
                DB::raw("SUM(CASE WHEN a.status = 'Hadir'      THEN 1 ELSE 0 END) AS hadir"),
                DB::raw("SUM(CASE WHEN a.status = 'Cuti'       THEN 1 ELSE 0 END) AS cuti"),
                DB::raw("SUM(CASE WHEN a.status = 'Sakit'      THEN 1 ELSE 0 END) AS sakit"),
                DB::raw("SUM(CASE WHEN a.status = 'Tugas Luar' THEN 1 ELSE 0 END) AS tugas_luar"),
                DB::raw("SUM(CASE WHEN a.status = 'Terlambat'  THEN 1 ELSE 0 END) AS terlambat"),
                DB::raw("SUM(CASE WHEN a.status = 'Izin'       THEN 1 ELSE 0 END) AS izin")
            )
            ->whereNotNull('u.bidang')
            ->groupBy('u.bidang')
            ->get()
            ->keyBy('bidang')
            ->toArray();

        $firebasePath = 'rekap/' . $tanggal;

        try {
            $this->database->getReference($firebasePath)->set($rekapData);
        } catch (\Exception $e) {
            \Log::error('Firebase update failed: ' . $e->getMessage());
        }
    }

    public function statistik(Request $request)
    {
        $user  = $request->user();
        $bulan = $request->input('bulan', now()->format('Y-m'));
        $tz    = config('app.timezone', 'Asia/Makassar');

        // Ambil pengaturan poin dari database
        $poinConfig = Setting::get('poin', [
            'hadir'      => 1, 'terlambat'  => 0, 'izin'       => 0, 'sakit'      => 0,
            'cuti'       => 0, 'tugas_luar' => 0, 'alpha'      => -1
        ]);
        $cutoffTime = config('absensi.cutoff', '16:00:00');

        // Definisikan pemetaan dari status di database ke kunci di poinConfig
        $poinKeyMap = [
            'Hadir'       => 'hadir', 'Terlambat'   => 'terlambat', 'Izin'        => 'izin',
            'Sakit'       => 'sakit', 'Cuti'        => 'cuti', 'Tugas Luar'  => 'tugas_luar', 'Alpha'       => 'alpha',
        ];

        // Tentukan rentang hari untuk dihitung
        $carbonBulan = Carbon::parse($bulan.'-01', $tz);
        $maxHari = $carbonBulan->daysInMonth;
        if ($carbonBulan->isFuture()) {
            $maxHari = 0;
        } elseif ($carbonBulan->isSameMonth(now($tz))) {
            $maxHari = now($tz)->day;
        }

        // Ambil semua absensi pada bulan terpilih untuk efisiensi
        $allAbsensiBulan = Absensi::whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulan])->get()->groupBy('user_id');

        // =================================================================
        // KALKULASI UNTUK SEMUA PENGGUNA (PERINGKAT)
        // =================================================================
        $allUsers = User::where('role', '!=', 'admin')->get();
        $monthlyScores = [];

        foreach ($allUsers as $u) {
            $userAbsensiLoop = $allAbsensiBulan->get($u->id, collect());
            $totalPoinLoop = 0;

            for ($i = 1; $i <= $maxHari; $i++) {
                $tanggalLoop = $carbonBulan->copy()->day($i);
                $absen = $userAbsensiLoop->first(fn($item) => Carbon::parse($item->tanggal)->isSameDay($tanggalLoop));

                if ($absen) {
                    $status = $absen->status;
                    $key = $poinKeyMap[$status] ?? null;
                    if ($key && isset($poinConfig[$key])) {
                        if ($status === 'Terlambat' && empty(trim($absen->alasan ?? ''))) {
                            $totalPoinLoop += (int)($poinConfig['alpha'] ?? 0);
                        } else {
                            $totalPoinLoop += (int)($poinConfig[$key] ?? 0);
                        }
                    }
                } else {
                    // LOGIKA BARU YANG BENAR:
                    // Hitung alpha jika hari sudah lewat, ATAU jika hari ini & sudah lewat jam cutoff
                    if ($tanggalLoop->isPast() || ($tanggalLoop->isToday() && now($tz)->format('H:i:s') > $cutoffTime)) {
                        $totalPoinLoop += (int)($poinConfig['alpha'] ?? 0);
                    }
                }
            }
            $monthlyScores[] = (object)['nama' => $u->nama, 'poin_total' => $totalPoinLoop];
        }

        $scoresCollection = collect($monthlyScores);
        $top5Global = $scoresCollection->sortByDesc('poin_total')->take(5)->values();
        $bottom5Global = $scoresCollection->sortBy('poin_total')->take(5)->values();

        // =================================================================
        // KALKULASI UNTUK USER LOGIN (DIAGRAM DONAT)
        // =================================================================
        $absensi = $allAbsensiBulan->get($user->id, collect());
        $totalPoin = 0; // Ini untuk donut
        $rekapData = array_fill_keys(array_keys($poinKeyMap), 0);
        $rekapData['Tanpa Keterangan'] = 0;

        for ($i = 1; $i <= $maxHari; $i++) {
            $tanggalLoop = $carbonBulan->copy()->day($i);
            $absen = $absensi->first(fn($item) => Carbon::parse($item->tanggal)->isSameDay($tanggalLoop));

            if ($absen) {
                $status = $absen->status;
                if (isset($rekapData[$status])) $rekapData[$status]++;
                
                $key = $poinKeyMap[$status] ?? null;
                if ($key && isset($poinConfig[$key])) {
                    if ($status === 'Terlambat' && empty(trim($absen->alasan ?? ''))) {
                        $totalPoin += (int)($poinConfig['alpha'] ?? 0);
                    } else {
                        $totalPoin += (int)($poinConfig[$key] ?? 0);
                    }
                }
            } else {
                $rekapData['Tanpa Keterangan']++;
                // LOGIKA BARU YANG BENAR:
                if ($tanggalLoop->isPast() || ($tanggalLoop->isToday() && now($tz)->format('H:i:s') > $cutoffTime)) {
                    $totalPoin += (int)($poinConfig['alpha'] ?? 0);
                }
            }
        }
        unset($rekapData['Alpha']);

        $adaData = array_sum($rekapData) > 0;

        return view('statistik', [
            'user'          => $user,
            'absensi'       => $absensi,
            'top5Global'    => $top5Global,
            'bottom5Global' => $bottom5Global,
            'poinConfig'    => $poinConfig,
            'totalPoin'     => $totalPoin,
            'rekapData'     => $rekapData,
            'adaData'       => $adaData,
            'statusColors'  => [
                'Hadir' => '#36A2EB', 'Izin' => '#FFCE56', 'Cuti' => '#9966FF',
                'Sakit' => '#FF6384', 'Terlambat' => '#4BC0C0', 'Tugas Luar' => '#FF9F40',
                'Tanpa Keterangan' => '#e0e0e0'
            ],
        ]);
    }

    public function exportCsvUser(Request $r): StreamedResponse
    {
        $user = $r->user();
        $bulan = $r->input('bulan', now()->format('Y-m'));
        $tz = config('app.timezone', 'Asia/Makassar');
        $filename = 'rekap_absensi_' . $user->username . '_' . $bulan . '.csv';

        $carbonBulan = Carbon::parse($bulan . '-01', $tz);
        $maxHari = $carbonBulan->daysInMonth;
        if ($carbonBulan->isFuture()) {
            $maxHari = 0;
        } elseif ($carbonBulan->isSameMonth(now($tz))) {
            $maxHari = now($tz)->day;
        }

        $absensiBulan = Absensi::where('user_id', $user->id)
            ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulan])
            ->get()
            ->keyBy('tanggal');

        $cutoffTime = config('absensi.cutoff', '16:00:00');

        return response()->streamDownload(function () use ($absensiBulan, $carbonBulan, $maxHari, $tz, $cutoffTime) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Tanggal', 'Status', 'Jam', 'Alasan']);

            for ($i = 1; $i <= $maxHari; $i++) {
                $tanggalLoop = $carbonBulan->copy()->day($i);
                $tanggalString = $tanggalLoop->toDateString();
                $absen = $absensiBulan->get($tanggalString);

                if ($absen) {
                    fputcsv($out, [
                        $absen->tanggal,
                        $absen->status,
                        $absen->jam,
                        $absen->alasan,
                    ]);
                } else {
                    if ($tanggalLoop->isPast() || ($tanggalLoop->isToday() && now($tz)->format('H:i:s') > $cutoffTime)) {
                        fputcsv($out, [
                            $tanggalString,
                            'Tanpa Keterangan',
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