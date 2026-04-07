<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function account()
    {
        $user = auth()->user();

        // Jika dipanggil lewat route admin.account, pakai view khusus admin
        if (request()->routeIs('admin.account')) {
            return view('admin.account', compact('user'));
        }

        // Default: view akun untuk user biasa
        return view('account', compact('user'));
    }
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        $user = auth()->user();

        // hapus foto lama kalau ada
        if ($user->foto && Storage::disk('public')->exists($user->foto)) {
            Storage::disk('public')->delete($user->foto);
        }

        // simpan foto baru
        $path = $request->file('foto')->store('profile', 'public');

        $user->foto = $path;
        $user->save();

        return back()->with('success', 'Foto profile berhasil diperbarui.');
    }

    public function deletePhoto()
    {
        $user = auth()->user();

        // hapus file lama kalau ada
        if ($user->foto && Storage::disk('public')->exists($user->foto)) {
            Storage::disk('public')->delete($user->foto);
        }

        // reset ke default
        $user->foto = null;
        $user->save();

        return back()->with('success', 'Foto profile berhasil dikembalikan ke default.');
    }

    /**
     * Mengupdate informasi dasar akun pengguna (nama).
     */
    public function updateAccount(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        $user->update($validated);

        return redirect()->route('account')->with('ok', 'Informasi akun berhasil diperbarui.');
    }

    /**
     * Mengupdate password pengguna yang sedang login.
     */
    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'password_lama' => 'required|string',
            'password_baru' => ['required', 'string', 'confirmed', Password::min(8)],
        ]);

        if (!Hash::check($validated['password_lama'], $user->password)) {
            return back()->withErrors(['password_lama' => 'Password lama yang Anda masukkan tidak sesuai.'])->withInput();
        }

        $user->update(['password' => Hash::make($validated['password_baru'])]);

        return redirect()->route('account')->with('ok', 'Password berhasil diperbarui.');
    }
}