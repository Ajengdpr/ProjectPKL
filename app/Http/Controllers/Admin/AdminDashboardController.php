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

        // 1. Data dasar
        $totalPegawai = User::count();
        $sudahAbsenUserIds = DB::table('absensi')
            ->whereDate('tanggal', $date)
            ->pluck('user_id');

        // 2. Statistik absensi
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
        $tugasLuar = $stats->get('tugas luar', 0) + $stats->get('tugasLuar', 0);

        // 3. Hitung Alpha
        $belumAbsenQuery = User::whereNotIn('id', $sudahAbsenUserIds);
        $alpha = (clone $belumAbsenQuery)->count();

        // Pagination Pegawai Belum Absen
        $belumAbsen = $belumAbsenQuery
            ->orderBy('nama')
            ->paginate(5, ['*'], 'belum_page');

        // 4. Log Absensi Terbaru (pagination)
        $logTerbaru = Absensi::with('user')
            ->whereDate('tanggal', $date)
            ->orderByDesc('id')
            ->paginate(5, ['*'], 'log_page');

        // 5. Ringkasan per Bidang
        $totalPerBidang = User::select('bidang', DB::raw('COUNT(1) as total'))
            ->whereNotNull('bidang')->where('bidang', '!=', '')
            ->groupBy('bidang')
            ->pluck('total', 'bidang');

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

        return view('admin.dashboard', compact(
            'date', 'totalPegawai', 'hadir', 'terlambat', 'izin', 'sakit',
            'alpha', 'cuti', 'tugasLuar',
            'logTerbaru', 'belumAbsen', 'byBidang'
        ));
    }
}