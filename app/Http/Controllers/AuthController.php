<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function loginPage()
    {
        return view('auth.login');
    }

public function login(Request $request)
{
    // Validasi input
    $cred = $request->validate([
        'username' => 'required|string',
        'password' => 'required|string',
    ]);

    // Cari user by username
    $user = User::where('username', $cred['username'])->first();

    if (!$user) {
        return back()->withErrors(['msg' => 'Username tidak ditemukan'])->withInput(['username']);
    }

    // Helper untuk redirect sesuai role
    $redirectByRole = function ($user, Request $request) {
        // bersihkan intended agar tidak “balik” ke /account
        $request->session()->forget('url.intended');

        $role = $user->role ?? 'user'; // default user jika kolom role belum ada/masih null
        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');   // /admin
        }
        return redirect()->route('dashboard');             // /dashboard (user biasa)
    };

    // 1) Password sudah bcrypt
    if (strlen($user->password) > 32 && Hash::check($cred['password'], $user->password)) {
        Auth::login($user, $request->has('remember'));
        $request->session()->regenerate();
        return $redirectByRole($user, $request);
    }

    // 2) Password legacy MD5 (32 hex)
    $looksMd5 = strlen($user->password) === 32 && preg_match('/^[a-f0-9]{32}$/i', $user->password);

    if ($looksMd5 && md5($cred['password']) === strtolower($user->password)) {
        // Upgrade ke bcrypt
        $user->password = Hash::make($cred['password']);
        $user->save();

        Auth::login($user, $request->has('remember'));
        $request->session()->regenerate();
        return $redirectByRole($user, $request);
    }

    // 3) Gagal
    return back()->withErrors(['msg' => 'Password salah'])->withInput(['username']);
}


    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}