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

        // 1. Statistik Utama
        $totalPegawai = User::count();
        $stats = Absensi::whereDate('tanggal', '2025-10-01') // Menggunakan tanggal statis untuk contoh
            ->select('status', DB::raw('COUNT(1) as jumlah'))
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        $hadir = $stats->get('hadir', 0);
        $terlambat = $stats->get('terlambat', 0);
        $izin = $stats->get('izin', 0);
        $sakit = $stats->get('sakit', 0);
        $alpha = $stats->get('alpha', 0);
        $cuti = $stats->get('cuti', 0); // <-- BARIS BARU
        $tugas_luar = $stats->get('tugas luar', 0); // <-- BARIS BARU
        
        $hadirTotal = $hadir + $terlambat;
        $attendanceRate = $totalPegawai ? round(($hadirTotal / $totalPegawai) * 100, 1) : 0.0;

        // 2. Log Absensi Terbaru
        $logTerbaru = Absensi::with('user')
            ->whereDate('tanggal', $date)
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        // 3. Daftar Pegawai yang Belum Absen
        $sudahAbsenUserIds = Absensi::whereDate('tanggal', $date)->pluck('user_id');
        $belumAbsenQuery = User::whereNotIn('id', $sudahAbsenUserIds);
        
        $belumAbsenCount = (clone $belumAbsenQuery)->count();
        $belumAbsen = $belumAbsenQuery->orderBy('nama')->limit(20)->get();

        // 4. Ringkasan per Bidang
        $totalPerBidang = User::select('bidang', DB::raw('COUNT(1) as total'))
            ->whereNotNull('bidang')->where('bidang', '!=', '')
            ->groupBy('bidang')->pluck('total', 'bidang');

        $statsPerBidang = Absensi::whereDate('tanggal', $date)
            ->join('users', 'users.id', '=', 'absensi.user_id')
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

        // Data dikirim ke view
        return view('admin.dashboard', compact(
            'date', 'totalPegawai', 'hadir', 'terlambat', 'izin', 'sakit', 'alpha', 'cuti', 'tugas_luar',
            'hadirTotal', 'attendanceRate', 'logTerbaru', 'belumAbsen', 'belumAbsenCount', 'byBidang'
        ));
    }
}