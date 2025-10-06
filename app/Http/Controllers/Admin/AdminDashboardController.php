<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Absensi;
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
        $cutoffTime = config('absensi.cutoff', '16:00:00');

        // Default alpha ke 0
        $alpha = 0;

        // Cek kondisi kapan alpha harus dihitung
        $isWeekend = $carbonDate->isWeekend();
        $isFuture = $carbonDate->isFuture();
        $isTodayBeforeCutoff = $carbonDate->isToday() && (now($tz)->format('H:i:s') <= $cutoffTime);

        // Ambil ID user yang sudah absen pada tanggal yang dipilih
        $sudahAbsenUserIds = DB::table('absensi')->whereDate('tanggal', $date)->pluck('user_id');

        // Ambil semua user yang belum absen, kecuali admin
        $belumAbsenQuery = User::whereNotIn('id', $sudahAbsenUserIds)->where('role', '!=', 'admin');

        $belumAbsen = collect();
        $belumAbsenCount = 0;

        // Logika untuk menampilkan daftar "Belum Absen"
        // Tampilkan jika bukan hari libur dan bukan tanggal di masa depan
        if (!$isWeekend && !$isFuture) {
            $belumAbsen = (clone $belumAbsenQuery)->orderBy('nama')->get();
            $belumAbsenCount = (clone $belumAbsenQuery)->count();
        }

        // Hitung alpha hanya jika ini adalah hari kerja yang sudah lewat, atau hari ini setelah jam cutoff
        if (!$isWeekend && !$isFuture && !$isTodayBeforeCutoff) {
            $alpha = $belumAbsenCount;
        }

        // 4. Log Absensi Terbaru
        $logTerbaru = Absensi::with('user')
            ->whereDate('tanggal', $date)
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        // 5. Ringkasan per Bidang (tidak perlu diubah, karena sudah pakai DB::raw)
        $totalPerBidang = User::select('bidang', DB::raw('COUNT(1) as total'))
            ->whereNotNull('bidang')->where('bidang', '!=', '')
            ->groupBy('bidang')->pluck('total', 'bidang');

        $statsPerBidang = DB::table('absensi')
            ->join('users', 'users.id', '=', 'absensi.user_id')
            ->whereDate('absensi.tanggal', $date)
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

        // Data lengkap dikirim ke view
        return view('admin.dashboard', compact(
            'date', 'totalPegawai', 'hadir', 'terlambat', 'izin', 'sakit', 'alpha', 'cuti', 'tugas_luar',
            'logTerbaru', 'belumAbsen', 'belumAbsenCount', 'byBidang'
        ));
    }
}