@extends('layouts.app')

@section('content')
<div class="card mx-auto max-w-3xl">
  <div class="card-body">
    <h2 class="h4 mb-3">Notifikasi</h2>

    @forelse ($items as $n)
      <div class="border rounded p-3 mb-2 d-flex justify-content-between align-items-center">
        <div>
          <div class="fw-semibold">{{ data_get($n->data, 'title') }}</div>
          <div class="text-muted small">
            {{ data_get($n->data, 'body') }}
          </div>
          <div class="text-muted small">
            {{ \Carbon\Carbon::parse($n->created_at)->diffForHumans() }}
          </div>
          
          {{-- Tombol Lihat Lampiran --}}
          @if(isset($n->data['berkas']) && $n->data['berkas'])
            <div class="mt-2">
              <a href="{{ asset('storage/' . $n->data['berkas']) }}" target="_blank" class="btn btn-sm btn-outline-primary shadow-sm">
                <i class="bi bi-file-earmark-text me-1"></i> Lihat Lampiran
              </a>
            </div>
          @endif
        </div>
        @if(is_null($n->read_at))
          <span class="badge bg-primary">baru</span>
        @endif
      </div>
    @empty
      <div class="text-center text-muted py-5">Belum ada notifikasi.</div>
    @endforelse

    <div class="mt-3">
      {{ $items->links() }}
    </div>
  </div>
</div>
@endsection