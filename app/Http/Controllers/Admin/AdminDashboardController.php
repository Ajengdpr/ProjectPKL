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
        $filterBidang = $request->query('bidang');  // filter belum-absen
        $q = $request->query('q');                  // search nama belum-absen

        $totalPegawai = User::count();

        $stat = Absensi::whereDate('tanggal', $date)
            ->select('status', DB::raw('COUNT(*) jml'))
            ->groupBy('status')
            ->pluck('jml','status');

        $hadir      = $stat['hadir']      ?? 0;
        $terlambat  = $stat['terlambat']  ?? 0;
        $izin       = $stat['izin']       ?? 0;
        $sakit      = $stat['sakit']      ?? 0;
        $alpha      = $stat['alpha']      ?? 0;

        $hadirTotal = $hadir + $terlambat;
        $attendanceRate = $totalPegawai ? round(($hadirTotal/$totalPegawai)*100,1) : 0.0;

        $logTerbaru = Absensi::with('user')
            ->whereDate('tanggal', $date)
            ->orderByDesc('id')
            ->limit(25)
            ->get();

        // ---- Belum absen (dengan filter bidang & search) ----
        $sudahAbsenIds = Absensi::whereDate('tanggal', $date)->pluck('user_id');
        $belumAbsenBase = User::when($sudahAbsenIds->isNotEmpty(), fn($q2) => $q2->whereNotIn('id', $sudahAbsenIds))
            ->when($filterBidang, fn($q2) => $q2->where('bidang', $filterBidang))
            ->when($q, fn($q2) => $q2->where('nama','like',"%{$q}%"));
        $belumAbsenCount = (clone $belumAbsenBase)->count();
        $belumAbsen = $belumAbsenBase->orderBy('nama')->limit(30)->get();

        // ---- Tren 7 hari (hadir + terlambat) ----
        $start = now()->parse($date)->subDays(6)->toDateString();
        $trend = Absensi::selectRaw('DATE(tanggal) as tgl, COUNT(DISTINCT user_id) as hadir')
            ->whereBetween(DB::raw('DATE(tanggal)'), [$start, $date])
            ->whereIn('status', ['hadir','terlambat'])
            ->groupBy('tgl')->orderBy('tgl')->get();

        // ---- Ringkasan per Bidang ----
        $totalPerBidang = User::select('bidang', DB::raw('COUNT(*) total'))
            ->groupBy('bidang')->pluck('total','bidang');

        $hadirPerBidang = Absensi::whereDate('tanggal', $date)
            ->whereIn('status',['hadir','terlambat'])
            ->join('users','users.id','=','absensi.user_id')
            ->selectRaw('users.bidang as bidang, COUNT(DISTINCT absensi.user_id) as hadir')
            ->groupBy('users.bidang')->pluck('hadir','bidang');

        $byBidang = [];
        foreach ($totalPerBidang as $bidang => $tot) {
            $h = $hadirPerBidang[$bidang] ?? 0;
            $byBidang[] = [
                'bidang' => $bidang ?? '—',
                'hadir'  => $h,
                'total'  => $tot,
                'rate'   => $tot ? round($h * 100 / $tot, 1) : 0.0,
            ];
        }
        usort($byBidang, fn($a,$b) => $b['rate'] <=> $a['rate']);

        // untuk dropdown filter bidang
        $allBidangs = User::select('bidang')->whereNotNull('bidang')->distinct()->orderBy('bidang')->pluck('bidang');

        return view('admin.dashboard', [
            'date'            => $date,
            'totalPegawai'    => $totalPegawai,
            'hadir'           => $hadir,
            'terlambat'       => $terlambat,
            'izin'            => $izin,
            'sakit'           => $sakit,
            'alpha'           => $alpha,
            'hadirTotal'      => $hadirTotal,
            'attendanceRate'  => $attendanceRate,
            'logTerbaru'      => $logTerbaru,
            'belumAbsen'      => $belumAbsen,
            'belumAbsenCount' => $belumAbsenCount,
            'trend'           => $trend,
            'byBidang'        => $byBidang,
            'allBidangs'      => $allBidangs,
            'filterBidang'    => $filterBidang,
            'q'               => $q,
        ]);
    }
}