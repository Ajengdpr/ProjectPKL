<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAbsensiController extends Controller
{
    public function index(Request $r)
    {
        $q = Absensi::with('user')
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        if ($r->filled('from'))    $q->whereDate('tanggal', '>=', $r->from);
        if ($r->filled('to'))      $q->whereDate('tanggal', '<=', $r->to);
        if ($r->filled('user_id')) $q->where('user_id', $r->user_id);
        if ($r->filled('bidang'))  $q->whereHas('user', fn($u) => $u->where('bidang', $r->bidang));
        if ($r->filled('status'))  $q->where('status', $r->status);  // << filter status

        $absensi = $q->paginate(20)->withQueryString();

        $users   = User::orderBy('nama')->get();
        $bidangs = User::select('bidang')->whereNotNull('bidang')->distinct()->pluck('bidang');

        return view('admin.absensi.index', compact('absensi', 'users', 'bidangs'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'user_id' => 'required|exists:users,id',
            'tanggal' => 'required|date',
            'status'  => 'required|in:hadir,terlambat,izin,sakit,alpha',
            'alasan'  => 'nullable|string',
        ]);
        Absensi::create($data);
        return back()->with('ok', 'Absensi ditambahkan.');
    }

    public function edit(Absensi $absensi)
    {
        return view('admin.absensi.edit', compact('absensi'));
    }

    public function update(Request $r, Absensi $absensi)
    {
        $data = $r->validate([
            'tanggal' => 'required|date',
            'status'  => 'required|in:hadir,terlambat,izin,sakit,alpha',
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
}