<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

class AdminAbsensiController extends Controller
{
    public function index(Request $r)
    {
        $q = trim($r->get('q'));

        $query = Absensi::with('user')
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        if ($q) {
            $query->where(function ($subquery) use ($q) {
                $subquery->where('alasan', 'like', "%{$q}%")
                        ->orWhereHas('user', function ($userQuery) use ($q) {
                            $userQuery->where('nama', 'like', "%{$q}%");
                        });
            });
        }
        if ($r->filled('from'))    $query->whereDate('tanggal', '>=', $r->from);
        if ($r->filled('to'))      $query->whereDate('tanggal', '<=', $r->to);
        if ($r->filled('user_id')) $query->where('user_id', $r->user_id);
        if ($r->filled('bidang'))  $query->whereHas('user', fn($u) => $u->where('bidang', $r->bidang));
        if ($r->filled('status'))  $query->where('status', $r->status);

        $absensi = $query->paginate(20)->withQueryString();

        $users   = User::orderBy('nama')->get();
        $bidangs = User::select('bidang')->whereNotNull('bidang')->distinct()->pluck('bidang');

        return view('admin.absensi.index', compact('absensi', 'users', 'bidangs'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'user_id' => 'required|exists:users,id',
            'tanggal' => 'required|date',
            'status'  => 'required|in:hadir,terlambat,izin,sakit,alpha,cuti,tugas_luar',
            'alasan'  => 'nullable|string',
        ]);
        Absensi::create($data);
        return back()->with('ok', 'Absensi ditambahkan.');
    }

    public function update(Request $r, Absensi $absensi)
    {
        $data = $r->validate([
            'tanggal' => 'required|date',
            'status'  => 'required|in:hadir,terlambat,izin,sakit,alpha,cuti,tugas_luar',
            'alasan'  => 'nullable|string',
        ]);
        $absensi->update($data);
        return back()->with('ok', 'Absensi diperbarui.');
    }

    public function destroy(Absensi $absensi)
    {
        $absensi->delete();
        return back()->with('ok', 'Absensi dihapus.');
    }

    public function exportCsv(Request $r): StreamedResponse
    {
        $filename = 'absensi_'.now()->format('Ymd_His').'.csv';

        $rows = Absensi::with('user')
            ->when($r->filled('from'), fn($q)=>$q->whereDate('tanggal','>=',$r->from))
            ->when($r->filled('to'),   fn($q)=>$q->whereDate('tanggal','<=',$r->to))
            ->when($r->filled('user_id'), fn($q)=>$q->where('user_id',$r->user_id))
            ->when($r->filled('bidang'),  fn($q)=>$q->whereHas('user', fn($u)=>$u->where('bidang',$r->bidang)))
            ->when($r->filled('status'),  fn($q)=>$q->where('status',$r->status)) // ikut filter status
            ->orderBy('tanggal')->orderBy('id')
            ->get();

        return response()->streamDownload(function() use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Tanggal','Nama','Username','Status','Alasan']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    optional($r->tanggal)->format('Y-m-d'),
                    $r->user->nama ?? '',
                    $r->user->username ?? '',
                    strtoupper($r->status),
                    $r->alasan,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function exportCsvUser(Request $r): StreamedResponse
{
    // Validasi parameter
    $r->validate([
        'user_id' => 'required|exists:users,id',
        'bulan' => 'required|date_format:Y-m', // Validasi format bulan
    ]);

    $user = User::findOrFail($r->user_id);
    $bulan = $r->bulan;
    $tz = config('app.timezone', 'Asia/Makassar');
    $filename = 'rekap_absensi_' . $user->username . '_' . $bulan . '.csv';

    // Ambil data absensi sesuai bulan yang dipilih
    $carbonBulan = Carbon::parse($bulan . '-01', $tz);
    $maxHari = $carbonBulan->daysInMonth;
    if ($carbonBulan->isFuture()) {
        $maxHari = 0;
    } elseif ($carbonBulan->isSameMonth(now($tz))) {
        $maxHari = now($tz)->day;
    }

    $absensiBulan = Absensi::where('user_id', $user->id)
        ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulan])
        ->get()
        ->keyBy(fn($item) => Carbon::parse($item->tanggal)->toDateString());

    // Batas waktu (cutoff) absen
    $cutoffTime = config('absensi.cutoff', '16:00:00');

    // Stream CSV download
    return response()->streamDownload(function () use ($absensiBulan, $carbonBulan, $maxHari, $tz, $cutoffTime) {
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Tanggal', 'Status', 'Jam', 'Alasan']);

        // Loop untuk setiap hari di bulan tersebut
        for ($i = 1; $i <= $maxHari; $i++) {
            $tanggalLoop = $carbonBulan->copy()->day($i);
            $tanggalString = $tanggalLoop->toDateString();
            $absen = $absensiBulan->get($tanggalString);

            if ($absen) {
                fputcsv($out, [
                    $absen->tanggal,
                    $absen->status,
                    $absen->jam,
                    $absen->alasan,
                ]);
            } else {
                // Jika tanggal sudah lewat atau hari ini dan melewati cutoff time, beri keterangan
                if ($tanggalLoop->isPast() || ($tanggalLoop->isToday() && now($tz)->format('H:i:s') > $cutoffTime)) {
                    fputcsv($out, [
                        $tanggalString,
                        'Tanpa Keterangan',
                        '',
                        '',
                    ]);
                }
            }
        }
        fclose($out);
    }, $filename, ['Content-Type' => 'text/csv']);
}

}
