@extends('layouts.app')
@section('content')
<div class="max-w-3xl mx-auto p-4">
  <h1 class="text-2xl font-semibold mb-4">Edit Absensi</h1>
  <form method="post" action="{{ route('admin.absensi.update',$absensi) }}" class="space-y-3">
    @csrf @method('put')
    <input type="date" name="tanggal" value="{{ old('tanggal',$absensi->tanggal?->format('Y-m-d')) }}" class="border rounded px-3 py-2 w-full" required>
    <select name="status" class="border rounded px-3 py-2 w-full" required>
      @foreach(['hadir','terlambat','izin','sakit','alpha'] as $s)
        <option value="{{ $s }}" @selected($absensi->status===$s)>{{ ucfirst($s) }}</option>
      @endforeach
    </select>
    <input name="alasan" value="{{ old('alasan',$absensi->alasan) }}" class="border rounded px-3 py-2 w-full" placeholder="Alasan">
    <button class="px-4 py-2 bg-blue-600 text-white rounded">Simpan</button>
  </form>
</div>
@endsection