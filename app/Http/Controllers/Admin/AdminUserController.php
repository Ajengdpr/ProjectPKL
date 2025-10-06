<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $bidang = $request->query('bidang');

        $users = User::query()
            ->when($request->filled('q'), function ($query) use ($q) {
                $lowerQ = strtolower(trim($q));
                $query->where(function ($subQuery) use ($lowerQ) {
                    $subQuery->where(\Illuminate\Support\Facades\DB::raw('LOWER(nama)'), 'like', "%{$lowerQ}%")
                             ->orWhere(\Illuminate\Support\Facades\DB::raw('LOWER(username)'), 'like', "%{$lowerQ}%")
                             ->orWhere(\Illuminate\Support\Facades\DB::raw('LOWER(jabatan)'), 'like', "%{$lowerQ}%")
                             ->orWhere(\Illuminate\Support\Facades\DB::raw('LOWER(bidang)'), 'like', "%{$lowerQ}%");
                });
            })
            ->when($request->filled('bidang'), function ($query) use ($bidang) {
                $query->where('bidang', $bidang);
            })
            ->orderBy('nama')
            ->paginate(12)
            ->withQueryString();

        $listBidang = User::query()
            ->select('bidang')
            ->whereNotNull('bidang')
            ->where('bidang', '!=', '')
            ->distinct()
            ->orderBy('bidang')
            ->pluck('bidang')
            ->all();

        return view('admin.users.index', [
            'users' => $users,
            'q' => $request->query('q', ''),
            'bidang' => $request->query('bidang', ''),
            'listBidang' => $listBidang
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'     => 'required|string|max:100',
            'username' => 'required|string|max:100|unique:users,username',
            'jabatan'  => 'nullable|string|max:100',
            'bidang'   => 'nullable|string|max:100',
            'password' => 'required|string|min:4',
        ]);

        $data['password'] = Hash::make($data['password']);
        User::create($data);

        return back()->with('ok', 'Pegawai berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'nama'     => 'required|string|max:100',
            'username' => 'required|string|max:100|unique:users,username,' . $user->id,
            'jabatan'  => 'nullable|string|max:100',
            'bidang'   => 'nullable|string|max:100',
        ]);

        $user->update($data);

        return back()->with('ok', 'Data pegawai diperbarui.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('ok', 'Pegawai dihapus.');
    }

    public function resetPassword(User $user)
    {
        $user->password = Hash::make('123456');
        $user->save();

        return back()->with('ok', "Password {$user->nama} di-reset ke 123456.");
    }
}