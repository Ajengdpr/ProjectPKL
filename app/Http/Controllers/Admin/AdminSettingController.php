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

        $poin   = Setting::get('poin',   ['hadir'=>1,'terlambat'=>0,'izin'=>0,'sakit'=>0,'alpha'=>-1]);
        $lokasi = Setting::get('lokasi', ['lat'=>0,'lng'=>0,'radius'=>100]);
        $jam    = Setting::get('jam',    ['batas_hadir'=>'08:00:00']);

        return view('admin.settings.index', compact('tab','user','poin','lokasi','jam'));
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'poin' => 'array',
            'lokasi.lat' => 'nullable|numeric',
            'lokasi.lng' => 'nullable|numeric',
            'lokasi.radius' => 'nullable|numeric',
            'jam.batas_hadir' => 'required',
        ]);

        Setting::set('poin',   $data['poin']   ?? []);
        Setting::set('lokasi', $data['lokasi'] ?? []);
        Setting::set('jam',    $data['jam']    ?? []);

        return back()->with('ok', 'Pengaturan disimpan');
    }
}