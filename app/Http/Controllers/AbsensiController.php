<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Contract\Database; // <-- TAMBAHKAN: Import Firebase Database

class AbsensiController extends Controller
{
    private const ALLOWED_STATUSES = [
        'Hadir', 'Izin', 'Cuti', 'Sakit', 'Terlambat', 'Tugas Luar',
    ];

    // <-- TAMBAHKAN: Property untuk menampung instance Firebase Database
    protected $database;

    // <-- TAMBAHKAN: Constructor untuk otomatis mendapatkan instance Firebase
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    // ... (Method index() tidak perlu diubah, biarkan seperti semula)
    public function index(Request $request)
    {
        $user     = $request->user();
        $tz       = 'Asia/Makassar';
        $today    = now($tz)->toDateString();
        $tanggal  = $request->input('tanggal', $today);

        $log = DB::table('absensi')
            ->where('user_id', $user->id)
            ->orderByDesc('tanggal')
            ->orderByDesc('jam')
            ->limit(50)
            ->get();

        $rekapUser = DB::table('absensi')
            ->selectRaw('status, COUNT(*) AS total')
            ->where('user_id', $user->id)
            ->groupBy('status')
            ->pluck('total', 'status');

        $daftarBidang = DB::table('users as u')
            ->select('u.bidang', DB::raw('COUNT(*) AS jumlah_pegawai'))
            ->whereNotNull('u.bidang')
            ->groupBy('u.bidang')
            ->orderBy('u.bidang')
            ->get();

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

        $office = [
            'lat'    => (float) env('OFFICE_LAT', -3.489179),
            'lng'    => (float) env('OFFICE_LNG', 114.828158),
            'radius' => (int)   env('OFFICE_RADIUS', 200),
        ];

        $sudahAbsenToday = DB::table('absensi')
            ->where('user_id', $user->id)
            ->whereDate('tanggal', $today)
            ->exists();

        $lastToday = DB::table('absensi')
            ->where('user_id', $user->id)
            ->whereDate('tanggal', $today)
            ->orderByDesc('jam')
            ->first();

        $hadirDisabled = now('Asia/Makassar')->format('H:i') > '08:00';

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
        $today = now($tz)->toDateString();
        
        // ... (Logika validasi Anda tetap sama)
        $data = $request->validate([
            'status' => ['required', 'string'],
            'alasan' => ['nullable', 'string', 'max:255'],
        ]);
        $status = trim($data['status']);
        if (!in_array($status, self::ALLOWED_STATUSES, true)) {
            return back()->withErrors('Status tidak valid.');
        }
        if (Absensi::where('user_id', $user->id)->whereDate('tanggal', $today)->exists()) {
            return redirect()->route('dashboard')->with('err', 'Anda sudah absen hari ini, data tidak bisa diubah.');
        }
        if ($request->status === 'Hadir' && now('Asia/Makassar')->format('H:i') > '08:00') {
            return redirect()->route('dashboard')->with('err', 'Absen Hadir ditutup setelah 08:00 WITA.');
        }
        
        // Simpan data absensi ke database utama Anda
        $absen = new Absensi();
        $absen->user_id = $user->id;
        $absen->tanggal = $today;
        $absen->jam     = now($tz)->format('H:i:s');
        $absen->status  = $status;
        $absen->alasan  = $data['alasan'] ?? null;
        $absen->save();

        // Update point
        // ... (Logika update point Anda tetap sama)
        $delta = 0;
        if ($status === 'Hadir') $delta = 1;
        elseif ($status === 'Terlambat') $delta = (isset($data['alasan']) && trim($data['alasan']) !== '') ? -3 : -5;
        if ($delta !== 0) {
            DB::table('users')->where('id', $user->id)->update(['point' => DB::raw("point + ($delta)")]);
        }
        
        // <-- MODIFIKASI: Panggil method untuk update rekap ke Firebase setelah data tersimpan
        $this->updateFirebaseRekap($today);

        return redirect()->route('dashboard')
            ->with('ok', "Absensi {$status} tersimpan.");
    }

    // <-- TAMBAHKAN: Method baru untuk menghitung dan mengirim rekap ke Firebase
    private function updateFirebaseRekap(string $tanggal)
    {
        // 1. Ambil data rekap terbaru (logika query sama persis seperti di method index)
        $rekapData = DB::table('users as u')
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
            ->whereNotNull('u.bidang')
            ->groupBy('u.bidang')
            ->get()
            ->keyBy('bidang') // Kunci array berdasarkan nama bidang
            ->toArray();       // Ubah menjadi array

        // 2. Tentukan path di Firebase (misal: rekap/2025-09-25)
        $firebasePath = 'rekap/' . $tanggal;

        // 3. Kirim data ke Firebase Realtime Database
        try {
            $this->database->getReference($firebasePath)->set($rekapData);
        } catch (\Exception $e) {
            // Jika gagal, catat error agar tidak mengganggu alur utama aplikasi
            \Log::error('Firebase update failed: ' . $e->getMessage());
        }
    }


    // ... (Method statistik() tidak perlu diubah, biarkan seperti semula)
    public function statistik(Request $request)
    {
        $user  = $request->user();
        $bulan = $request->input('bulan', now()->format('Y-m'));
        $absensi = Absensi::where('user_id', $user->id)
            ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulan])
            ->orderBy('tanggal')
            ->get();
        $top5Global = DB::table('users')->select('nama', 'point as poin_total')->orderByDesc('point')->limit(5)->get();
        $bottom5Global = DB::table('users')->select('nama', 'point as poin_total')->orderBy('point')->limit(5)->get();
        return view('statistik', [
            'user'          => $user,
            'absensi'       => $absensi,
            'top5Global'    => $top5Global,
            'bottom5Global' => $bottom5Global,
        ]);
    }
}