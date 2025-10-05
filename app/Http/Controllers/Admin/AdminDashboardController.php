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
        $carbonDate = \Carbon\Carbon::parse($date);

        // 1. Jika hari libur, semua statistik nol
        if ($carbonDate->isWeekend()) {
            $hadir = $terlambat = $izin = $sakit = $alpha = $cuti = $tugas_luar = $belumAbsenCount = 0;
            $logTerbaru = collect();
            $belumAbsen = collect();
            $byBidang = [];
            $totalPegawai = User::count();

            return view('admin.dashboard', compact(
                'date', 'totalPegawai', 'hadir', 'terlambat', 'izin', 'sakit', 'alpha', 'cuti', 'tugas_luar',
                'logTerbaru', 'belumAbsen', 'belumAbsenCount', 'byBidang'
            ));
        }

        // --- Jika Hari Kerja ---

        // 2. Ambil data dasar
        $totalPegawai = User::count();
        $absensiHariIni = Absensi::whereDate('tanggal', $date)->get();
        $sudahAbsenUserIds = $absensiHariIni->pluck('user_id')->unique();

        // 3. Hitung statistik berdasarkan data yang sudah masuk
        $stats = $absensiHariIni->countBy(fn($item) => strtolower($item->status));

        $hadir = $stats->get('hadir', 0);
        $terlambat = $stats->get('terlambat', 0);
        $izin = $stats->get('izin', 0);
        $sakit = $stats->get('sakit', 0);
        $cuti = $stats->get('cuti', 0);
        $tugas_luar = $stats->get('tugas luar', 0);
        
        // 4. Hitung "Tanpa Keterangan" (Alpha) dengan logika yang KONSISTEN
        $alphaFromDb = $stats->get('alpha', 0);
        $alphaFromNoRecord = User::whereNotIn('id', $sudahAbsenUserIds)->count();
        $alpha = $alphaFromDb + $alphaFromNoRecord;
        
        $belumAbsenCount = $alpha;
        $belumAbsen = User::whereNotIn('id', $sudahAbsenUserIds)->orderBy('nama')->limit(20)->get();

        // 5. Log Absensi Terbaru
        $logTerbaru = $absensiHariIni->sortByDesc('id')->take(10);

        // 6. Ringkasan per Bidang
        $usersByBidang = User::whereNotNull('bidang')->where('bidang', '!=', '')->get()->groupBy('bidang');
        
        $statsPerBidang = DB::table('absensi')
            ->join('users', 'users.id', '=', 'absensi.user_id')
            ->whereDate('absensi.tanggal', $date)
            ->select(
                'users.bidang',
                DB::raw("LOWER(absensi.status) as status"),
                DB::raw("COUNT(1) as jumlah")
            )
            ->groupBy('users.bidang', 'absensi.status')
            ->get();

        $byBidang = [];
        foreach ($usersByBidang as $namaBidang => $usersInBidang) {
            $total = $usersInBidang->count();
            $userIdsInBidang = $usersInBidang->pluck('id');

            // Ambil statistik untuk bidang saat ini
            $statsForCurrentBidang = $statsPerBidang->where('bidang', $namaBidang);
            $h = $statsForCurrentBidang->where('status', 'hadir')->first()->jumlah ?? 0;
            $t = $statsForCurrentBidang->where('status', 'terlambat')->first()->jumlah ?? 0;
            
            // Logika Alpha yang konsisten
            $alphaFromDb = $statsForCurrentBidang->where('status', 'alpha')->first()->jumlah ?? 0;
            $sudahAbsenInBidang = $absensiHariIni->whereIn('user_id', $userIdsInBidang)->pluck('user_id')->unique();
            $alphaFromNoRecord = $userIdsInBidang->diff($sudahAbsenInBidang)->count();
            $a = $alphaFromDb + $alphaFromNoRecord;

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