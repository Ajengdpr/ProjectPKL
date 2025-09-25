<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AbsensiController extends Controller
{
    private const ALLOWED_STATUSES = [
        'Hadir', 'Izin', 'Cuti', 'Sakit', 'Terlambat', 'Tugas Luar',
    ];

    public function index(Request $request)
    {
        $user     = $request->user();
        $tz       = 'Asia/Makassar'; // Zona waktu
        $today    = now($tz)->toDateString(); // Waktu berdasarkan zona waktu
        $tanggal  = $request->input('tanggal', $today);

        // log absensi user
        $log = DB::table('absensi')
            ->where('user_id', $user->id)
            ->orderByDesc('tanggal')
            ->orderByDesc('jam')
            ->limit(50)
            ->get();

        // rekap per status untuk user
        $rekapUser = DB::table('absensi')
            ->selectRaw('status, COUNT(*) AS total')
            ->where('user_id', $user->id)
            ->groupBy('status')
            ->pluck('total', 'status');

        // daftar bidang & jumlah pegawai
        $daftarBidang = DB::table('users as u')
            ->select('u.bidang', DB::raw('COUNT(*) AS jumlah_pegawai'))
            ->whereNotNull('u.bidang')
            ->groupBy('u.bidang')
            ->orderBy('u.bidang')
            ->get();

        // rekap absensi per bidang (harian)
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

        // data kantor untuk geolocation
        $office = [
            'lat'    => (float) env('OFFICE_LAT', -3.489179),
            'lng'    => (float) env('OFFICE_LNG', 114.828158),
            'radius' => (int)   env('OFFICE_RADIUS', 200),
        ];

        // ----- Tambahan untuk tampilan UI
        $sudahAbsenToday = DB::table('absensi')
            ->where('user_id', $user->id)
            ->whereDate('tanggal', $today)
            ->exists();

        $lastToday = DB::table('absensi')
            ->where('user_id', $user->id)
            ->whereDate('tanggal', $today)
            ->orderByDesc('jam')
            ->first();

        $hadirDisabled = now('Asia/Makassar')->format('H:i') > '10:00';

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
        $user = $request->user();
        $tz   = 'Asia/Makassar';

        $data = $request->validate([
            'status' => ['required', 'string'],
            'alasan' => ['nullable', 'string', 'max:255'],
        ]);

        $status = trim($data['status']);
        if (!in_array($status, self::ALLOWED_STATUSES, true)) {
            return back()->withErrors('Status tidak valid.');
        }

        $today = now($tz)->toDateString();

        // Cek sudah absen hari ini
        $sudah = Absensi::where('user_id', $user->id)
            ->whereDate('tanggal', $today)
            ->exists();

        if ($sudah) {
            return redirect()->route('dashboard')
                ->with('err', 'Anda sudah absen hari ini, data tidak bisa diubah.');
        }

        // Blokir "Hadir" setelah 08:00 WITA (SERVER-SIDE)
        if ($request->status === 'Hadir') {
            if (now('Asia/Makassar')->format('H:i') > '10:00') {
                return redirect()->route('dashboard')
                    ->with('err', 'Absen Hadir ditutup setelah 10:00 WITA.');
            }
        }

        // Simpan data absensi (pakai waktu WITA)
        $absen = new Absensi();
        $absen->user_id = $user->id;
        $absen->tanggal = $today;
        $absen->jam     = now($tz)->format('H:i:s');
        $absen->status  = $status;
        $absen->alasan  = $data['alasan'] ?? null;
        $absen->save();

        // Update point
        $delta = 0;
        if ($status === 'Hadir') {
            $delta = 1;
        } elseif ($status === 'Terlambat') {
            $delta = (isset($data['alasan']) && trim($data['alasan']) !== '') ? -3 : -5;
        }
        if ($delta !== 0) {
            DB::table('users')->where('id', $user->id)
                ->update(['point' => DB::raw("point + ($delta)")]);
        }

        return redirect()->route('dashboard')
            ->with('ok', "Absensi {$status} tersimpan.");
    }

    public function statistik(Request $request)
    {
        $user  = $request->user();
        $bulan = $request->input('bulan', now()->format('Y-m'));

        $absensi = Absensi::where('user_id', $user->id)
            ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulan])
            ->orderBy('tanggal')
            ->get();

        // Ranking 5 tertinggi
        $top5Global = DB::table('users')
            ->select('nama', 'point as poin_total')
            ->orderByDesc('point')
            ->limit(5)
            ->get();

        // Ranking 5 terendah
        $bottom5Global = DB::table('users')
            ->select('nama', 'point as poin_total')
            ->orderBy('point')
            ->limit(5)
            ->get();

        return view('statistik', [
            'user'          => $user,
            'absensi'       => $absensi,
            'top5Global'    => $top5Global,
            'bottom5Global' => $bottom5Global,
        ]);
    }
}
