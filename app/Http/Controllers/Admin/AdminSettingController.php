<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class AdminSettingController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'account');
        $user = $request->user();

        $poin   = Setting::get('poin', [
            'Hadir'=>1,
            'Terlambat'=>0,
            'Izin'=>0,
            'Sakit'=>0,
            'Cuti'=>0,
            'Tugas Luar'=>0,
            'alpha'=>-1
        ]);

        $lokasi = Setting::get('lokasi', [
            'lat' => null,
            'lng' => null,
            'radius' => 100
        ]);

        // ✅ Gunakan array_merge untuk memastikan semua key ada meskipun DB memiliki struktur lama
        $jam = array_merge([
            'buka' => '07:00:00',
            'batas_hadir' => '08:00:00',
            'batas_akhir' => '16:00:00',
        ], Setting::get('jam', []));

        $status = Setting::get('status', [
            'reason' => 'Hari Libur Nasional / Kantor Tutup',
            'hari_libur' => '',
        ]);

        return view('admin.settings.index', compact('tab','user','poin','lokasi','jam','status'));
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'poin' => 'array',
            'lokasi.lat' => 'required|numeric',
            'lokasi.lng' => 'required|numeric',
            'lokasi.radius' => 'required|numeric',
            'jam.buka' => 'required',
            'jam.batas_hadir' => 'required',
            'jam.batas_akhir' => 'required',
            'status.reason' => 'nullable|string|max:255',
            'status.hari_libur' => 'nullable|string',
        ]);

        $jam = [
            'buka' => $data['jam']['buka'] ?? '07:00:00',
            'batas_hadir' => $data['jam']['batas_hadir'] ?? '08:00:00',
            'batas_akhir' => $data['jam']['batas_akhir'] ?? '16:00:00',
        ];

        $status = [
            'reason' => $data['status']['reason'] ?? 'Hari Libur Nasional / Kantor Tutup',
            'hari_libur' => $data['status']['hari_libur'] ?? '',
        ];

        Setting::set('poin',   $data['poin']   ?? []);
        Setting::set('lokasi', $data['lokasi'] ?? []);
        Setting::set('jam',    $jam);
        Setting::set('status', $status);

        return back()->with('ok', 'Pengaturan disimpan');
    }
}