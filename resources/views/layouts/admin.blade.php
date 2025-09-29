{{-- Bungkus layout utama dan sisipkan bottom-nav-admin --}}
@extends('layouts.app')

@section('content')
  <div class="has-bottom-nav">
    @yield('content')
  </div>

  {{-- Nav bawah khusus admin --}}
  @include('partials.bottom-nav-admin')
@endsection
