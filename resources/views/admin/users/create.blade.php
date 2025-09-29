@extends('layouts.app')
@section('content')
<div class="max-w-3xl mx-auto p-4">
  <h1 class="text-2xl font-semibold mb-4">Tambah Pegawai</h1>
  <form method="post" action="{{ route('admin.users.store') }}" class="space-y-3">@csrf
    <input name="nama" class="border rounded px-3 py-2 w-full" placeholder="Nama" required>
    <input name="username" class="border rounded px-3 py-2 w-full" placeholder="Username" required>
    <input type="password" name="password" class="border rounded px-3 py-2 w-full" placeholder="Password" required>
    <input name="jabatan" class="border rounded px-3 py-2 w-full" placeholder="Jabatan">
    <input name="bidang" class="border rounded px-3 py-2 w-full" placeholder="Bidang">
    <select name="role" class="border rounded px-3 py-2 w-full">
      <option value="user">User</option>
      <option value="admin">Admin</option>
    </select>
    <button class="px-4 py-2 bg-blue-600 text-white rounded">Simpan</button>
  </form>
</div>
@endsection