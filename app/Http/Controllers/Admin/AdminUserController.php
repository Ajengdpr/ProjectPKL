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
        $q      = trim($request->get('q', ''));
        $bidang = $request->get('bidang', '');

        $users = User::query()
            ->when($q, function ($query) use ($q) {
                $query->where(function ($x) use ($q) {
                    $x->where('nama', 'like', "%{$q}%")
                      ->orWhere('username', 'like', "%{$q}%")
                      ->orWhere('jabatan', 'like', "%{$q}%")
                      ->orWhere('bidang', 'like', "%{$q}%");
                });
            })
            ->when($bidang !== '', fn($q2) => $q2->where('bidang', $bidang))
            ->orderBy('nama') // pastikan kolomnya `nama`, bukan `name`
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

        return view('admin.users.index', compact('users', 'q', 'bidang', 'listBidang'));
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
            'password' => 'nullable|string|min:4',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

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