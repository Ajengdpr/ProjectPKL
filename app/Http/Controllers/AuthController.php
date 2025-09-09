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

        // Cari user berdasarkan username (karena tabelmu pakai username, bukan email)
        $user = User::where('username', $cred['username'])->first();

        if (!$user) {
            return back()->withErrors(['msg' => 'Username tidak ditemukan'])->withInput(['username']);
        }

        // 1) Jika sudah bcrypt (hash panjang, bukan 32 char md5), cek dengan Hash::check
        if (strlen($user->password) > 32 && Hash::check($cred['password'], $user->password)) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        // 2) Jika masih MD5 (32 char hex), verifikasi manual lalu upgrade ke bcrypt
        $looksMd5 = strlen($user->password) === 32 && preg_match('/^[a-f0-9]{32}$/i', $user->password);

        if ($looksMd5 && md5($cred['password']) === strtolower($user->password)) {
            // Upgrade ke bcrypt
            $user->password = Hash::make($cred['password']);
            $user->save();

            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
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