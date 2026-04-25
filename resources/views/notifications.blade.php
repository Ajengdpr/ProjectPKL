@extends('layouts.app')
@section('title', 'Notifikasi')

@push('head')
<style>
    :root {
        --primary-blue: #0d6efd;
        --border-color: #f1f5f9;
        --bg-light: #f8fafc;
        --text-dark: #1e293b;
        --text-muted: #64748b;
        --section-gap: 2rem;
    }

    body {
        background-color: #f1f5f9;
    }

    .notif-page-container {
        width: 100%;
        margin-top: 0;
        padding-bottom: 5rem;
    }

    /* Modern Header Style */
    .header-section {
        background: linear-gradient(135deg, #0d6efd 0%, #003d99 100%);
        padding: 2.5rem 3rem;
        border-radius: 24px;
        border: none;
        box-shadow: 0 10px 25px rgba(0, 61, 153, 0.15);
        margin-bottom: var(--section-gap);
        color: white;
    }

    .header-content h3 {
        font-weight: 800;
        margin: 0;
        color: white;
        letter-spacing: -0.5px;
    }

    .header-content p {
        margin: 0;
        color: rgba(255, 255, 255, 0.8);
        font-weight: 500;
    }

    /* Notification Card Styling */
    .notif-card-wrapper {
        background: white;
        border-radius: 24px;
        border: 1px solid var(--border-color);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
        overflow: hidden;
    }

    .notif-list-item {
        padding: 1.75rem 2rem;
        border-bottom: 1px solid #f1f5f9;
        transition: all 0.2s ease;
        display: flex;
        gap: 1.5rem;
        position: relative;
    }

    .notif-list-item:last-child {
        border-bottom: none;
    }

    .notif-list-item:hover {
        background-color: #fcfdfe;
    }

    .notif-list-item.unread {
        background-color: rgba(13, 110, 253, 0.02);
    }

    /* Dot indicator for unread */
    .unread-indicator {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        width: 8px;
        height: 8px;
        background-color: var(--primary-blue);
        border-radius: 50%;
        box-shadow: 0 0 10px rgba(13, 110, 253, 0.4);
    }

    .notif-icon-circle {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
        background: var(--bg-light);
        color: #94a3b8;
        transition: all 0.3s;
    }

    .unread .notif-icon-circle {
        background: rgba(13, 110, 253, 0.1);
        color: var(--primary-blue);
        transform: scale(1.05);
    }

    .notif-info h6 {
        font-weight: 800;
        color: var(--text-dark);
        margin-bottom: 0.25rem;
        font-size: 1.05rem;
    }

    .notif-info p {
        color: #475569;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 0.75rem;
        font-weight: 500;
    }

    .notif-meta {
        font-size: 0.8rem;
        color: #94a3b8;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 700;
    }

    .btn-attachment-modern {
        border-radius: 12px;
        padding: 0.6rem 1.25rem;
        font-weight: 800;
        font-size: 0.8rem;
        margin-top: 0.5rem;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
    }

    .empty-state-container {
        padding: 6rem 2rem;
        text-align: center;
    }

    .empty-state-icon {
        font-size: 4.5rem;
        color: #e2e8f0;
        margin-bottom: 1.5rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-md-4 notif-page-container">
    {{-- Modern Header --}}
    <div class="header-section">
        <div class="header-content">
            <h3>Notifikasi</h3>
            <p>Kelola semua pemberitahuan dan aktivitas sistem presensi Anda.</p>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="notif-card-wrapper">
        @forelse ($items as $n)
            @php
                $isUnread = is_null($n->read_at);
                $title = data_get($n->data, 'title', 'Pemberitahuan');
                $body = data_get($n->data, 'body', '-');
            @endphp
            
            <div class="notif-list-item {{ $isUnread ? 'unread' : '' }}">
                @if($isUnread)
                    <div class="unread-indicator"></div>
                @endif
                
                <div class="notif-icon-circle">
                    <i class="bi bi-bell-fill"></i>
                </div>
                
                <div class="notif-info w-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <h6>{{ $title }}</h6>
                        @if($isUnread)
                            <span class="badge rounded-pill bg-primary px-3 py-2" style="font-size: 0.65rem; font-weight: 800; letter-spacing: 0.5px;">BARU</span>
                        @endif
                    </div>
                    
                    <p>{{ $body }}</p>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="notif-meta">
                            <i class="bi bi-clock-fill text-primary opacity-50"></i>
                            {{ \Carbon\Carbon::parse($n->created_at)->locale('id')->diffForHumans() }}
                        </div>
                        
                        @if(isset($n->data['berkas']) && $n->data['berkas'])
                            <a href="{{ asset('storage/' . $n->data['berkas']) }}" target="_blank" class="btn btn-outline-primary btn-sm btn-attachment-modern shadow-sm">
                                <i class="bi bi-file-earmark-arrow-down-fill"></i> Lihat Lampiran
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state-container">
                <div class="empty-state-icon"><i class="bi bi-inbox-fill"></i></div>
                <h5 class="fw-bold text-dark">Belum ada notifikasi</h5>
                <p class="text-muted">Pemberitahuan mengenai aktivitas Anda akan muncul di sini.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $items->links() }}
    </div>
</div>
@endsection
