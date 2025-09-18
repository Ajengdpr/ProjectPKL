<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAbsensiRequest;
use App\Models\Absensi;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

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
    $user = auth()->user(); 

    $log = Absensi::where('user_id', $user->id)
        ->orderByDesc('tanggal')->orderByDesc('jam')
        ->limit(10)
        ->get();

    $rekap = Absensi::selectRaw('status, COUNT(*) as total')
        ->where('user_id', $user->id)
        ->groupBy('status')
        ->pluck('total', 'status'); 

    $office = [
        'lat'    => (float) env('OFFICE_LAT', -3.319437),
        'lng'    => (float) env('OFFICE_LNG', 114.590844),
        'radius' => (int)   env('OFFICE_RADIUS', 200), // meter
    ];

    return view('dashboard', compact('user', 'log', 'rekap', 'office'));
}

    public function create(string $status)
    {
        $label = self::STATUS_LABELS[$status] ?? null;
        abort_unless($label, 404);
        return view('absensi.form', ['preset' => $label, 'required' => $label === 'Terlambat']);
    }

    public function store(StoreAbsensiRequest $request)
    {
        $userId = auth()->id();
        $today  = now()->toDateString();

        // Blokir absen ganda di hari yang sama (status apa pun)
        $sudahAda = Absensi::where('user_id', $userId)
            ->where('tanggal', $today)
            ->exists();

        if ($sudahAda) {
            return back()->withErrors(['msg' => 'Anda sudah absen hari ini, data tidak bisa diubah.']);
        }

        // Simpan absensi
        $absen = Absensi::create([
            'user_id' => $userId,
            'tanggal' => $today,
            'jam'     => now()->toTimeString(),
            'status'  => $request->status,
            'alasan'  => $request->alasan ?: null,
        ]);

        // === Hitung & terapkan poin ===
        $delta = 0;
        if ($request->status === 'Hadir') {
            $delta = +1;
        } elseif ($request->status === 'Terlambat') {
            $delta = trim((string)$request->alasan) === '' ? -5 : -3;
        }

        if ($delta !== 0) {
            $user = User::find($userId); // ambil ulang user dari DB
            $user->point = $user->point + $delta;
            $user->save();
        }
        // ==============================


        return redirect()->route('dashboard')->with('ok', "Absensi {$absen->status} tersimpan.");
    }
}