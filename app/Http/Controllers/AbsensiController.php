<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AbsensiController extends Controller
{
    // mapping untuk penamaan konsisten
    private const ALLOWED_STATUSES = [
        'Hadir', 'Izin', 'Cuti', 'Sakit', 'Terlambat', 'Tugas Luar',
    ];

    public function index(Request $request)
    {
        $user    = $request->user();
        $tanggal = $request->input('tanggal', now()->toDateString());

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

        return view('dashboard', [
            'user'           => $user,
            'log'            => $log,
            'tanggal'        => $tanggal,
            'daftarBidang'   => $daftarBidang,
            'rekapPerBidang' => $rekapPerBidang,
            'rekap'          => $rekapUser,
            'office'         => $office,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        // validasi input dari form modal
        $data = $request->validate([
            'status' => ['required', 'string'],
            'alasan' => ['nullable', 'string', 'max:255'],
            // jika kamu kirim lat/lng dari frontend, bisa divalidasi juga:
            // 'lat' => ['nullable','numeric'],
            // 'lng' => ['nullable','numeric'],
        ]);

        // samakan kapitalisasi agar sesuai yang tersimpan di DB/rekap
        $status = trim($data['status']);
        // izinkan hanya status yang kita kenal
        if (!in_array($status, self::ALLOWED_STATUSES, true)) {
            return back()->withErrors('Status tidak valid.');
        }

        // Cegah absen ganda di hari yang sama
        $today = now()->toDateString();
        $sudah = Absensi::where('user_id', $user->id)
            ->whereDate('tanggal', $today)
            ->exists();

        if ($sudah) {
            return redirect()->route('dashboard')
                ->with('err', 'Anda sudah absen hari ini, data tidak bisa diubah.');
        }
    

        // Simpan absensi
        $absen = new Absensi();
        $absen->user_id = $user->id;
        $absen->tanggal = $today;
        $absen->jam     = now()->toTimeString();
        $absen->status  = $status;
        $absen->alasan  = $data['alasan'] ?? null;
        $absen->save();

        // Update point user
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
}