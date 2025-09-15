<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class AbsensiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Dashboard
     */
    public function index(Request $request)
    {
        $user    = $request->user();                       // user yang login
        $tanggal = $request->input('tanggal', now()->toDateString());

        // ----- LOG ABSENSI USER (terbaru)
        $log = DB::table('absensi')
            ->where('user_id', $user->id)
            ->orderByDesc('tanggal')
            ->orderByDesc('jam')
            ->limit(50)
            ->get();

        // ----- REKAP PER STATUS UNTUK USER (opsional; jaga kompatibilitas)
        $rekapUser = DB::table('absensi')
            ->selectRaw('status, COUNT(*) AS total')
            ->where('user_id', $user->id)
            ->groupBy('status')
            ->pluck('total', 'status');

        // ----- DAFTAR BIDANG & JUMLAH PEGAWAI
        $daftarBidang = DB::table('users as u')
            ->select('u.bidang', DB::raw('COUNT(*) AS jumlah_pegawai'))
            ->whereNotNull('u.bidang')
            ->groupBy('u.bidang')
            ->orderBy('u.bidang')
            ->get();

        // ----- REKAP ABSENSI PER BIDANG (HARIAN)
        $rekapPerBidang = DB::table('users as u')
            ->leftJoin('absensi as a', function ($join) use ($tanggal) {
                $join->on('a.user_id', '=', 'u.id')
                     ->whereDate('a.tanggal', $tanggal);   // filter per hari
            })
            ->select(
                'u.bidang',
                DB::raw("SUM(CASE WHEN a.status = 'Hadir'       THEN 1 ELSE 0 END) AS hadir"),
                DB::raw("SUM(CASE WHEN a.status = 'Cuti'        THEN 1 ELSE 0 END) AS cuti"),
                DB::raw("SUM(CASE WHEN a.status = 'Sakit'       THEN 1 ELSE 0 END) AS sakit"),
                DB::raw("SUM(CASE WHEN a.status = 'Tugas Luar'  THEN 1 ELSE 0 END) AS tugas_luar"),
                DB::raw("SUM(CASE WHEN a.status = 'Terlambat'   THEN 1 ELSE 0 END) AS terlambat"),
                DB::raw("SUM(CASE WHEN a.status = 'Izin'        THEN 1 ELSE 0 END) AS izin")
            )
            ->groupBy('u.bidang')
            ->get()
            ->keyBy('bidang'); // akses di blade: $rekapPerBidang[$bidang]

        return view('dashboard', [
            'user'           => $user,
            'log'            => $log,
            'tanggal'        => $tanggal,
            'daftarBidang'   => $daftarBidang,
            'rekapPerBidang' => $rekapPerBidang,
            'rekap'          => $rekapUser, // kalau blade lama masih pakai $rekap
        ]);
    }

    /**
     * Simpan absensi (1x per hari per user)
     */
    public function store(Request $request)
    {
        $request->validate([
            'status' => 'required|string',   // Hadir / Izin / Cuti / Sakit / Terlambat / Tugas Luar
            'alasan' => 'nullable|string',
        ]);

        $userId = $request->user()->id;
        $today  = now()->toDateString();
        $nowT   = now()->format('H:i:s');

        // ---- blokir absen ganda (hari yang sama, status apa pun)
        $sudahAda = DB::table('absensi')
            ->where('user_id', $userId)
            ->whereDate('tanggal', $today)
            ->exists();

        if ($sudahAda) {
            return back()->withErrors(['msg' => 'Anda sudah absen hari ini.']);
        }

        // ---- simpan absensi
        DB::table('absensi')->insert([
            'user_id'    => $userId,
            'tanggal'    => $today,
            'jam'        => $nowT,                         // jam sekarang
            'status'     => $request->input('status'),
            'alasan'     => $request->input('alasan') ?: null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ---- hitung & terapkan poin
        $delta = 0;
        $status = $request->input('status');
        if ($status === 'Hadir') {
            $delta = +1;
        } elseif ($status === 'Terlambat') {
            $delta = trim((string)$request->input('alasan')) === '' ? -5 : -3;
        }

        if ($delta !== 0) {
            DB::table('users')->where('id', $userId)->update([
                'point' => DB::raw("COALESCE(point,0) + ($delta)")
            ]);
        }

        return redirect()->route('dashboard')->with('ok', "Absensi {$status} tersimpan.");
    }
}
