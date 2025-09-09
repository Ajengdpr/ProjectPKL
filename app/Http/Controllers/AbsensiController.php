<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absensi;

class AbsensiController extends Controller
{
    public function index()
    {
        // contoh: tampilkan 30 log terakhir user & total per status
        $user = auth()->user();

        $log = Absensi::with('user')
            ->where('user_id',$user->id)
            ->orderByDesc('tanggal')->orderByDesc('jam')
            ->limit(30)->get();

        $rekap = Absensi::selectRaw("status, COUNT(*) as total")
            ->where('user_id',$user->id)
            ->groupBy('status')->pluck('total','status');

        return view('dashboard', compact('log','rekap','user'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'status' => 'required|in:Hadir,Izin,Sakit,Terlambat,Tugas Luar',
            'alasan' => 'nullable|string|max:500',
        ]);

        Absensi::create([
            'user_id' => auth()->user()->id,       // penting: jangan pakai auth()->id() kalau intelephense rewel
            'tanggal' => now()->toDateString(),
            'jam'     => now()->toTimeString(),
            'status'  => $data['status'],
            'alasan'  => $data['alasan'] ?? null,
        ]);

        return redirect()->route('dashboard')->with('ok','Absensi berhasil dicatat.');
    }
}