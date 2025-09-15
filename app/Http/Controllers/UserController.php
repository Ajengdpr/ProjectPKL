<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function account()
    {
        $user = auth()->user();
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
}