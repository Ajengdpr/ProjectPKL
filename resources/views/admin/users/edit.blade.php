
@extends('layouts.app')
@section('content')
<div class="max-w-3xl mx-auto p-4">
  <h1 class="text-2xl font-semibold mb-4">Edit Pegawai</h1>
  <form method="post" action="{{ route('admin.users.update',$user) }}" class="space-y-3">
    @csrf @method('put')
    <input name="nama" value="{{ old('nama',$user->nama) }}" class="border rounded px-3 py-2 w-full" required>
    <input name="username" value="{{ old('username',$user->username) }}" class="border rounded px-3 py-2 w-full" required>
    <input name="jabatan" value="{{ old('jabatan',$user->jabatan) }}" class="border rounded px-3 py-2 w-full">
    <input name="bidang" value="{{ old('bidang',$user->bidang) }}" class="border rounded px-3 py-2 w-full">
    <select name="role" class="border rounded px-3 py-2 w-full">
      <option value="user"  @selected($user->role==='user')>User</option>
      <option value="admin" @selected($user->role==='admin')>Admin</option>
    </select>
    <button class="px-4 py-2 bg-blue-600 text-white rounded">Simpan Perubahan</button>
  </form>
</div>
@endsection