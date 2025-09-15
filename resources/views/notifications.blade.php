@extends('layouts.app')
@section('title','Notifikasi')

@section('content')
<div class="container">

  <div class="app-card p-4 mx-auto" style="max-width:720px">
    <h4 class="fw-bold mb-3"><i class="bi bi-bell me-2"></i> Notifikasi</h4>

    <ul class="list-group list-group-flush">
      @forelse($items as $item)
        <li class="list-group-item d-flex justify-content-between align-items-start">
          <div>
            <div class="fw-semibold">{{ $item['title'] }}</div>
            <small class="text-muted">{{ $item['time'] }}</small>
          </div>
          <i class="bi bi-dot text-primary fs-4"></i>
        </li>
      @empty
        <li class="list-group-item text-center text-muted">Belum ada notifikasi</li>
      @endforelse
    </ul>
  </div>

</div>

{{-- Bottom nav --}}
<nav class="bottom-nav mt-4">
  <div class="container">
    <ul class="nav justify-content-around py-2">
      <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}"><i class="bi bi-house-door me-1"></i> Home</a></li>
      <li class="nav-item"><a class="nav-link active" href="{{ route('notifications') }}"><i class="bi bi-bell me-1"></i> Notifikasi</a></li>
      <li class="nav-item"><a class="nav-link" href="{{ route('account') }}"><i class="bi bi-person me-1"></i> Account</a></li>
    </ul>
  </div>
</nav>
@endsection