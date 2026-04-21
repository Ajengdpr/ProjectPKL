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
            'jam'     => 'required|date_format:H:i',
            'status'  => 'required|in:hadir,terlambat,izin,sakit,alpha,cuti,tugas_luar',
            'alasan'  => 'nullable|string',
            'berkas'  => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        ]);

        if ($r->hasFile('berkas')) {
            $data['berkas'] = $r->file('berkas')->store('absensi_berkas', 'public');
        }

        $absensi = Absensi::create($data);
        $this->syncUserPoint($absensi->user_id);
        
        return back()->with('ok', 'Absensi ditambahkan.');
    }

    public function update(Request $r, Absensi $absensi)
    {
        $data = $r->validate([
            'tanggal' => 'required|date',
            'status'  => 'required|in:hadir,terlambat,izin,sakit,alpha,cuti,tugas_luar',
            'alasan'  => 'nullable|string',
            'berkas'  => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        ]);

        if ($r->hasFile('berkas')) {
            // Hapus berkas lama jika ada
            if ($absensi->berkas && \Illuminate\Support\Facades\Storage::disk('public')->exists($absensi->berkas)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($absensi->berkas);
            }
            $data['berkas'] = $r->file('berkas')->store('absensi_berkas', 'public');
        }

        $absensi->update($data);
        $this->syncUserPoint($absensi->user_id);

        return back()->with('ok', 'Absensi diperbarui.');
    }

    public function destroy(Absensi $absensi)
    {
        $userId = $absensi->user_id;
        $absensi->delete();
        $this->syncUserPoint($userId);

        return back()->with('ok', 'Absensi dihapus.');
    }

    /**
     * Menghitung ulang total poin user berdasarkan log absensi bulan berjalan.
     * Ini memastikan tabel users.point selalu sinkron dengan tabel absensi.
     */
    private function syncUserPoint($userId)
    {
        $user = \App\Models\User::find($userId);
        if (!$user) return;

        $tz = 'Asia/Makassar';
        $bulan = now($tz)->format('Y-m');
        
        $poinConfig = \App\Models\Setting::get('poin', [
            'hadir' => 1, 'terlambat' => 0, 'izin' => 0,
            'sakit' => 0, 'cuti' => 0, 'tugas_luar' => 0, 'alpha' => -1
        ]);
        
        $statusConfig = \App\Models\Setting::get('status', ['hari_libur' => '']);
        $hariLibur = explode("\n", str_replace("\r", "", $statusConfig['hari_libur'] ?? ''));

        $absensiBulan = Absensi::where('user_id', $userId)
            ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulan])
            ->get();

        $carbonBulan = now($tz)->startOfMonth();
        $maxHari = $carbonBulan->isSameMonth(now($tz)) ? now($tz)->day : $carbonBulan->daysInMonth;
        
        $totalPoin = 0;
        $poinKeyMap = [
            'Hadir' => 'hadir', 'Terlambat' => 'terlambat', 'Izin' => 'izin',
            'Sakit' => 'sakit', 'Cuti' => 'cuti', 'Tugas Luar' => 'tugas_luar', 'alpha' => 'alpha'
        ];

        $cutoffTime = config('absensi.cutoff', '16:00:00');

        for ($i = 1; $i <= $maxHari; $i++) {
            $tanggalLoop = $carbonBulan->copy()->day($i);
            $tanggalString = $tanggalLoop->toDateString();

            if ($tanggalLoop->isWeekend() || in_array($tanggalString, $hariLibur)) {
                continue;
            }

            $absen = $absensiBulan->first(fn($item) => $item->tanggal == $tanggalString);

            if ($absen) {
                $status = $absen->status;
                $key = $poinKeyMap[$status] ?? null;
                if ($key && isset($poinConfig[$key])) {
                    if ($status === 'Terlambat' && empty(trim($absen->alasan ?? ''))) {
                        $totalPoin += (int)($poinConfig['alpha'] ?? 0);
                    } else {
                        $totalPoin += (int)($poinConfig[$key] ?? 0);
                    }
                }
            } else {
                // Logika Alpha
                $isTodayBeforeCutoff = $tanggalLoop->isToday() && (now($tz)->format('H:i:s') <= $cutoffTime);
                if (!$isTodayBeforeCutoff) {
                    $totalPoin += (int)($poinConfig['alpha'] ?? 0);
                }
            }
        }

        $user->update(['point' => $totalPoin]);
    }


    public function exportCsv(Request $r): StreamedResponse
    {
        $filename = 'absensi_'.now()->format('Ymd_His').'.csv';

        $rows = Absensi::with('user')
            ->when($r->filled('from'), fn($q)=>$q->whereDate('tanggal','>=',$r->from))
            ->when($r->filled('to'),   fn($q)=>$q->whereDate('tanggal','<=',$r->to))
            ->when($r->filled('user_id'), fn($q)=>$q->where('user_id',$r->user_id))
            ->when($r->filled('bidang'),  fn($q)=>$q->whereHas('user', fn($u)=>$u->where('bidang',$r->bidang)))
            ->when($r->filled('status'),  fn($q)=>$q->where('status',$r->status))
            ->orderBy('tanggal')->orderBy('id')
            ->get();

        return response()->streamDownload(function() use ($rows) {
            $out = fopen('php://output', 'w');
            
            // Menggunakan format lama (koma) tanpa BOM
            fputcsv($out, ['Tanggal', 'Nama', 'Username', 'Bidang', 'Status', 'Alasan']);

            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->tanggal,
                    $row->user->nama ?? '',
                    $row->user->username ?? '',
                    $row->user->bidang ?? '-',
                    strtoupper($row->status),
                    $row->alasan ?: '-',
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
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