<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Absensi;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
public function index(Request $request)
    {
        $date = $request->query('date', now()->toDateString());

        // 1. Ambil data dasar
        $totalPegawai = User::count();

        // 2. Hitung statistik berdasarkan data yang sudah masuk di tabel absensi
        // [FIX FINAL] Menggunakan LOWER(status) untuk mengatasi masalah case-sensitivity
        $stats = DB::table('absensi')
            ->whereDate('tanggal', $date)
            ->where('is_approved', true) // Hanya yang disetujui
            ->select(DB::raw('LOWER(status) as status'), DB::raw('COUNT(*) as jumlah'))
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        $hadir = $stats->get('hadir', 0);
        $terlambat = $stats->get('terlambat', 0);
        $izin = $stats->get('izin', 0);
        $sakit = $stats->get('sakit', 0);
        $cuti = $stats->get('cuti', 0);
        $tugas_luar = $stats->get('tugas luar', 0); // Sesuaikan dengan nilai di DB
        
        // 3. Hitung "Tanpa Keterangan" (Alpha) dengan logika yang benar dan kondisional
        $tz = 'Asia/Makassar';
        $carbonDate = \Carbon\Carbon::parse($date, $tz);
        
        // Gunakan jamConfig batas_hadir untuk Alpha
        $jamConfig = array_merge(['batas_hadir' => '08:00:00'], Setting::get('jam', []));
        $cutoffTime = $jamConfig['batas_hadir'];

        // Default alpha ke 0
        $alpha = 0;

        // Cek kondisi kapan alpha harus dihitung
        $isWeekend = $carbonDate->isWeekend();
        $isFuture = $carbonDate->isFuture();
        $isTodayBeforeCutoff = $carbonDate->isToday() && (now($tz)->format('H:i:s') <= $cutoffTime);

        // Ambil ID user yang sudah absen (dan disetujui) pada tanggal yang dipilih
        $sudahAbsenUserIds = DB::table('absensi')
            ->whereDate('tanggal', $date)
            ->where('is_approved', true)
            ->pluck('user_id');

        // Ambil semua user yang belum absen, kecuali admin
        $belumAbsenQuery = User::whereNotIn('id', $sudahAbsenUserIds)->where('role', '!=', 'admin');

        $belumAbsen = collect();
        $belumAbsenCount = 0;

        // Logika untuk menampilkan daftar "Belum Absen"
        // Tampilkan jika bukan hari libur dan bukan tanggal di masa depan
        if (!$isWeekend && !$isFuture) {
            $belumAbsen = (clone $belumAbsenQuery)->orderBy('bidang')->orderBy('nama')->get()->groupBy('bidang');
            $belumAbsenCount = (clone $belumAbsenQuery)->count();
        }

        // Hitung alpha hanya jika ini adalah hari kerja yang sudah lewat, atau hari ini setelah jam cutoff
        if (!$isWeekend && !$isFuture && !$isTodayBeforeCutoff) {
            $alpha = $belumAbsenCount;
        }

        // 4. Log Absensi Terbaru (Menampilkan semua di hari yang dipilih)
        $logTerbaru = Absensi::with('user')
            ->whereDate('tanggal', $date)
            ->orderByDesc('id')
            ->get();

        // 5. Ringkasan per Bidang (tidak perlu diubah, karena sudah pakai DB::raw)
        $totalPerBidang = User::select('bidang', DB::raw('COUNT(1) as total'))
            ->whereNotNull('bidang')->where('bidang', '!=', '')
            ->groupBy('bidang')->pluck('total', 'bidang');

        $statsPerBidang = DB::table('absensi')
            ->join('users', 'users.id', '=', 'absensi.user_id')
            ->whereDate('absensi.tanggal', $date)
            ->where('absensi.is_approved', true) // Hanya yang disetujui
            ->select(
                'users.bidang',
                DB::raw("COUNT(CASE WHEN absensi.status = 'hadir' THEN 1 END) as hadir"),
                DB::raw("COUNT(CASE WHEN absensi.status = 'terlambat' THEN 1 END) as terlambat"),
                DB::raw("COUNT(CASE WHEN absensi.status = 'alpha' THEN 1 END) as alpha")
            )
            ->groupBy('users.bidang')
            ->get()
            ->keyBy('bidang');

        $byBidang = [];
        foreach ($totalPerBidang as $namaBidang => $total) {
            $statBidang = $statsPerBidang->get($namaBidang);
            $h = $statBidang->hadir ?? 0;
            $t = $statBidang->terlambat ?? 0;
            $a = $statBidang->alpha ?? 0;
            
            $byBidang[] = [
                'bidang' => $namaBidang,
                'total' => $total,
                'hadir_total' => $h + $t,
                'hadir_total_rate' => $total ? round(($h + $t) * 100 / $total) : 0,
                'hadir_rate' => $total ? round($h * 100 / $total) : 0,
                'terlambat_rate' => $total ? round($t * 100 / $total) : 0,
                'alpha_rate' => $total ? round($a * 100 / $total) : 0,
            ];
        }
        usort($byBidang, fn($a, $b) => $b['hadir_total_rate'] <=> $a['hadir_total_rate']);

        // 6. Ranking Poin Pegawai (Sinkron Bulanan dengan Dashboard User)
        $rankingPoin = $this->getMonthlyRankingData();

        // Data lengkap dikirim ke view
        return view('admin.dashboard', compact(
            'date', 'totalPegawai', 'hadir', 'terlambat', 'izin', 'sakit', 'alpha', 'cuti', 'tugas_luar',
            'logTerbaru', 'belumAbsen', 'belumAbsenCount', 'byBidang', 'rankingPoin'
        ));
    }

    public function exportPoints()
    {
        $users = $this->getMonthlyRankingData();

        $filename = "Ranking_Poin_Pegawai_" . date('Y-m-d') . ".csv";
        $handle = fopen('php://output', 'w');

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Header CSV
        fputcsv($handle, ['Peringkat', 'Nama Pegawai', 'Username', 'Bidang', 'Jabatan', 'Total Poin (Bulan Ini)']);

        foreach ($users as $index => $u) {
            fputcsv($handle, [
                $index + 1,
                $u->nama,
                $u->username ?: '-',
                $u->bidang ?: '-',
                $u->jabatan ?: '-',
                $u->point
            ]);
        }

        fclose($handle);
        exit;
    }

    /**
     * Helper untuk menghitung poin bulanan seluruh pegawai secara live.
     * Logika ini disamakan persis dengan dashboard user.
     */
    private function getMonthlyRankingData()
    {
        $tz = config('absensi.timezone', 'Asia/Makassar');
        $jamConfig = array_merge(['batas_hadir' => '08:00:00'], Setting::get('jam', []));
        $cutoffStr = $jamConfig['batas_hadir'];
        $bulan = now($tz)->format('Y-m');
        
        $poinConfig = Setting::get('poin', [
            'hadir'      => 1,
            'terlambat'  => -3,
            'izin'       => 0,
            'sakit'      => 0,
            'cuti'       => 0,
            'tugas_luar' => 0,
            'alpha'      => -5
        ]);

        $statusConfig = Setting::get('status', [
            'hari_libur' => '',
        ]);
        $hariLiburRaw = $statusConfig['hari_libur'] ?? '';
        $hariLibur = array_filter(explode("\n", str_replace("\r", "", $hariLiburRaw)));

        $carbonBulan = now($tz)->startOfMonth();
        $maxHari = $carbonBulan->isSameMonth(now($tz)) ? now($tz)->day : $carbonBulan->daysInMonth;

        $workdaysSoFar = [];
        for ($i = 1; $i <= $maxHari; $i++) {
            $tgl = $carbonBulan->copy()->day($i);
            $tglStr = $tgl->toDateString();
            if ($tgl->isWeekend() || in_array($tglStr, $hariLibur)) {
                continue;
            }
            // Logika Alpha: Jika hari ini belum lewat cutoff, jangan hitung alpha dulu
            if ($tgl->isToday()) {
                if (now($tz)->format('H:i:s') > $cutoffStr) {
                    $workdaysSoFar[] = $tglStr;
                }
            } else {
                $workdaysSoFar[] = $tglStr;
            }
        }

        $allUsers = User::where('role', '!=', 'admin')->get(['id', 'nama', 'foto', 'bidang', 'jabatan', 'username']);
        $allAbsensi = Absensi::whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulan])
            ->where('is_approved', true) // Hanya yang disetujui
            ->get()
            ->groupBy('user_id');

        $rankingData = $allUsers->map(function($u) use ($allAbsensi, $workdaysSoFar, $poinConfig) {
            $userAbsensi = $allAbsensi->get($u->id, collect());
            $points = 0;
            $presentDates = [];

            foreach ($userAbsensi as $absen) {
                $presentDates[] = $absen->tanggal;
                $status = $absen->status;
                
                if ($status === 'Hadir') {
                    $points += (int)($poinConfig['hadir'] ?? 1);
                } elseif ($status === 'Terlambat') {
                    if (empty(trim($absen->alasan ?? ''))) {
                        $points += (int)($poinConfig['alpha'] ?? -5);
                    } else {
                        $points += (int)($poinConfig['terlambat'] ?? -3);
                    }
                } elseif ($status === 'Izin') {
                    $points += (int)($poinConfig['izin'] ?? 0);
                } elseif ($status === 'Sakit') {
                    $points += (int)($poinConfig['sakit'] ?? 0);
                } elseif ($status === 'Cuti') {
                    $points += (int)($poinConfig['cuti'] ?? 0);
                } elseif ($status === 'Tugas Luar') {
                    $points += (int)($poinConfig['tugas_luar'] ?? 0);
                } elseif ($status === 'alpha') {
                    $points += (int)($poinConfig['alpha'] ?? -5);
                }
            }

            // Kurangi poin (Alpha) untuk setiap hari kerja yang tidak ada catatan absensinya
            foreach ($workdaysSoFar as $wd) {
                if (!in_array($wd, $presentDates)) {
                    $points += (int)($poinConfig['alpha'] ?? -5);
                }
            }

            $u->point = $points;
            return $u;
        });

        return $rankingData->sortByDesc('point')->values();
    }
}