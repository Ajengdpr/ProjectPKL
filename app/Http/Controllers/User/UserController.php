<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function account()
    {
        $user = auth()->user();
        if (request()->routeIs('admin.account')) {
            return view('admin.account', compact('user'));
        }
        return view('user.account', compact('user'));
    }
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        $user = auth()->user();

        if ($user->foto && Storage::disk('public')->exists($user->foto)) {
            Storage::disk('public')->delete($user->foto);
        }

        $path = $request->file('foto')->store('profile', 'public');

        $user->foto = $path;
        $user->save();

        return back()->with('success', 'Foto profile berhasil diperbarui.');
    }

    public function deletePhoto()
    {
        $user = auth()->user();

        if ($user->foto && Storage::disk('public')->exists($user->foto)) {
            Storage::disk('public')->delete($user->foto);
        }

        $user->foto = null;
        $user->save();

        return back()->with('success', 'Foto profile berhasil dikembalikan ke default.');
    }
}