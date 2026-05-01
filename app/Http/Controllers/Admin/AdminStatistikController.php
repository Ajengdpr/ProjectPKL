<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Absensi;
use App\Models\Setting;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminStatistikController extends Controller
{
    public function index(Request $request)
    {
        $selectedUserId = $request->input('user_id');
        $bulan = $request->input('bulan', now()->format('Y-m'));
        $tz = 'Asia/Makassar';

        // Ambil semua pegawai untuk dropdown
        $users = User::where('role', '!=', 'admin')->orderBy('nama')->get();
        
        $selectedUser = null;
        $absensi = collect();
        $totalPoin = 0;
        $rekapData = [
            'Hadir'=>0, 'Izin'=>0, 'Cuti'=>0, 'Sakit'=>0, 'Terlambat'=>0, 'Tugas Luar'=>0, 'Tanpa Keterangan'=>0
        ];
        $adaData = false;
        $top5Global = collect();
        $bottom5Global = collect();

        $poinConfig = Setting::get('poin', [
            'hadir' => 1, 'terlambat' => 0, 'izin' => 0, 'sakit' => 0,
            'cuti' => 0, 'tugas_luar' => 0, 'alpha' => -1
        ]);
        $statusColors = [
            'Hadir' => '#36A2EB', 'Izin' => '#FFCE56', 'Cuti' => '#9966FF',
            'Sakit' => '#FF6384', 'Terlambat' => '#4BC0C0', 'Tugas Luar' => '#FF9F40',
            'Tanpa Keterangan' => '#e0e0e0'
        ];

        if ($selectedUserId) {
            $selectedUser = User::find($selectedUserId);
            
            if ($selectedUser) {
                // Logika Kalkulasi (Duplikasi dari AbsensiController@statistik)
                $poinKeyMap = [
                    'Hadir' => 'hadir', 'Terlambat' => 'terlambat', 'Izin' => 'izin',
                    'Sakit' => 'sakit', 'Cuti' => 'cuti', 'Tugas Luar' => 'tugas_luar', 'alpha' => 'alpha',
                ];

                $carbonBulan = Carbon::parse($bulan.'-01', $tz);
                $maxHari = $carbonBulan->daysInMonth;
                if ($carbonBulan->isFuture()) {
                    $maxHari = 0;
                } elseif ($carbonBulan->isSameMonth(now($tz))) {
                    $maxHari = now($tz)->day;
                }

                $absensi = Absensi::where('user_id', $selectedUserId)
                    ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulan])
                    ->get();

                $statusConfig = Setting::get('status', ['hari_libur' => '']);
                $hariLibur = explode("\n", str_replace("\r", "", $statusConfig['hari_libur'] ?? ''));
                $cutoffTime = config('absensi.cutoff', '16:00:00');

                for ($i = 1; $i <= $maxHari; $i++) {
                    $tanggalLoop = $carbonBulan->copy()->day($i);
                    $tanggalLoopString = $tanggalLoop->toDateString();

                    if ($tanggalLoop->isWeekend() || in_array($tanggalLoopString, $hariLibur)) {
                        continue;
                    }

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
                        $isTodayBeforeCutoff = $tanggalLoop->isToday() && (now($tz)->format('H:i:s') <= $cutoffTime);
                        if (!$isTodayBeforeCutoff) {
                            $rekapData['Tanpa Keterangan']++;
                            $totalPoin += (int)($poinConfig['alpha'] ?? 0);
                        }
                    }
                }
                $adaData = array_sum($rekapData) > 0;
            }
        }

        return view('admin.statistik', compact(
            'users', 'selectedUser', 'bulan', 'absensi', 
            'totalPoin', 'rekapData', 'adaData', 'poinConfig', 'statusColors'
        ));
    }
}
