<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAbsensiRequest;
use App\Models\Absensi;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    private const STATUS_LABELS = [
        'hadir'       => 'Hadir',
        'izin'        => 'Izin',
        'cuti'        => 'Cuti',
        'sakit'       => 'Sakit',
        'terlambat'   => 'Terlambat',
        'tugas-luar'  => 'Tugas Luar',
    ];

    public function index()
    {
        $user = User::findOrFail(auth()->id());
        $log = Absensi::where('user_id', $user->id)
            ->orderByDesc('tanggal')
            ->orderByDesc('jam')
            ->limit(50)
            ->get();

        $rekap = Absensi::selectRaw('status, COUNT(*) as total')
            ->where('user_id', $user->id)
            ->groupBy('status')
            ->pluck('total','status');

        // Tambahkan "Tanpa Keterangan" jika ada tanggal tanpa absensi
        $hariIni = Carbon::now('Asia/Makassar')->daysInMonth;
        $absensiCount = $rekap->sum();
        if($absensiCount < $hariIni){
            $rekap['Tanpa Keterangan'] = $hariIni - $absensiCount;
        }

        return view('dashboard', compact('user','log','rekap'));
    }

    public function create(string $status)
    {
        $label = self::STATUS_LABELS[$status] ?? null;
        abort_unless($label, 404);

        return view('absensi.form', [
            'preset'    => $label,
            'required'  => $label === 'Terlambat'
        ]);
    }

    public function store(StoreAbsensiRequest $request)
    {
        $userId = auth()->id();
        $today  = Carbon::now('Asia/Makassar')->toDateString();
        $currentTime = Carbon::now('Asia/Makassar');

        $sudahAda = Absensi::where('user_id', $userId)
            ->where('tanggal', $today)
            ->exists();

        if ($sudahAda) {
            return back()->withErrors(['msg' => 'Anda sudah absen hari ini, data tidak bisa diubah.']);
        }

        if ($request->status === 'Hadir' && $currentTime->hour >= 8) {
            return back()->withErrors(['msg' => 'Anda sudah tidak bisa absen Hadir, lewat jam 08.00 WITA']);
        }
        if ($request->status === 'Terlambat' && $currentTime->hour >= 16) {
            return back()->withErrors(['msg' => 'Anda sudah tidak bisa absen Terlambat, lewat jam 16.00 WITA']);
        }

        $absen = Absensi::create([
            'user_id' => $userId,
            'tanggal' => $today,
            'jam'     => $currentTime->toTimeString(),
            'status'  => $request->status,
            'alasan'  => $request->alasan ?: null,
        ]);

        // Hitung poin
        $delta = 0;
        if ($request->status === 'Hadir') $delta = +1;
        elseif ($request->status === 'Terlambat') $delta = -3;

        if ($delta !== 0) {
            $user = User::find($userId);
            $user->point += $delta;
            $user->save();
        }

        return redirect()->route('dashboard')->with('ok', "Absensi {$absen->status} tersimpan.");
    }

    public function statistik(Request $request)
    {
        $user = User::findOrFail(auth()->id());
        $bulan = $request->bulan ?? date('Y-m');
        $tahun = date('Y', strtotime($bulan));
        $bulanNum = date('m', strtotime($bulan));

        $absensi = Absensi::where('user_id', $user->id)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulanNum)
            ->orderBy('tanggal','asc')
            ->get();

        $hariDalamBulan = Carbon::parse($bulan.'-01')->daysInMonth;

        $rekap = Absensi::selectRaw('status, COUNT(*) as total')
            ->where('user_id', $user->id)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulanNum)
            ->groupBy('status')
            ->pluck('total','status');

        // Tambahkan "Tanpa Keterangan" jika ada tanggal tanpa absensi
        $absensiCount = $rekap->sum();
        if($absensiCount < $hariDalamBulan){
            $rekap['Tanpa Keterangan'] = $hariDalamBulan - $absensiCount;
        }

        // Hitung poin semua user
        $allUsers = User::with(['absensi' => function($q) use ($tahun, $bulanNum){
            $q->whereYear('tanggal',$tahun)->whereMonth('tanggal',$bulanNum);
        }])->get();

        foreach($allUsers as $u){
            $poinTotal = 0;
            $absensiUser = $u->absensi->keyBy('tanggal');
            for($i=1;$i<=$hariDalamBulan;$i++){
                $tgl = Carbon::parse($bulan.'-'.str_pad($i,2,'0',STR_PAD_LEFT))->format('Y-m-d');
                if(isset($absensiUser[$tgl])){
                    $status = $absensiUser[$tgl]->status;
                    if($status==='Hadir') $poinTotal += 1;
                    elseif($status==='Terlambat') $poinTotal -= 3;
                } else {
                    $poinTotal -= 5; // Tanpa Keterangan
                }
            }
            $u->poin_total = $poinTotal;
        }

        $top5Global = $allUsers->sortByDesc('poin_total')->take(5);
        $bottom5Global = $allUsers->sortBy('poin_total')->take(5);

        return view('statistik', compact(
            'user','absensi','rekap','top5Global','bottom5Global','bulan'
        ));
    }
}
