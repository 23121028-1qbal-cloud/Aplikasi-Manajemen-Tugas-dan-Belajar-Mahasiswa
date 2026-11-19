@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <div>
            <h2 class="h5 mb-1">Notifikasi</h2>
            <p class="small text-body-secondary mb-0">
                Pemberitahuan penugasan, deadline, dan aktivitas belajar milik akun ini.
            </p>
        </div>

        @if ($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-sm btn-primary">
                    Tandai semua dibaca
                </button>
            </form>
        @endif
    </div>

    <div class="app-card p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <p class="small text-body-secondary mb-0">
                {{ $notifications->total() }} notifikasi
                @if ($unreadCount > 0)
                    &middot; <strong>{{ $unreadCount }} belum dibaca</strong>
                @endif
            </p>
        </div>

        @forelse ($notifications as $notification)
            @php
                $type = $notification->tipe;
                $typeLabel = $type?->label() ?? 'Notifikasi';
                $typeIcon = match ($type) {
                    \App\Enums\NotificationType::AcademicTaskAssigned => 'bi-clipboard-check',
                    \App\Enums\NotificationType::AcademicTaskDeadline => 'bi-alarm',
                    \App\Enums\NotificationType::StudyActivityReminder => 'bi-journal-bookmark',
                    default => 'bi-bell',
                };
                $resourceUrl = $resourceUrls[$notification->id_notifikasi] ?? null;
            @endphp

            <article class="border rounded p-3 mb-2 {{ $notification->is_read ? '' : 'border-primary' }}"
                     aria-labelledby="notification-{{ $notification->id_notifikasi }}-title">
                <div class="d-flex gap-3 align-items-start">
                    <div class="text-primary fs-5" aria-hidden="true">
                        <i class="bi {{ $typeIcon }}"></i>
                    </div>

                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <span class="badge text-bg-light border">{{ $typeLabel }}</span>
                            @if ($notification->is_read)
                                <span class="badge text-bg-secondary">Dibaca</span>
                            @else
                                <span class="badge text-bg-primary">Belum dibaca</span>
                            @endif
                        </div>

                        <h3 class="h6 mb-1" id="notification-{{ $notification->id_notifikasi }}-title">
                            {{ $notification->judul }}
                        </h3>
                        <p class="mb-2">{{ $notification->pesan }}</p>

                        <p class="small text-body-secondary mb-2">
                            <time datetime="{{ $notification->tanggal_dibuat?->toIso8601String() }}">
                                {{ $notification->tanggal_dibuat?->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB
                            </time>
                        </p>

                        <div class="d-flex flex-wrap gap-2">
                            @if ($resourceUrl !== null)
                                <a href="{{ $resourceUrl }}" class="btn btn-sm btn-outline-secondary">
                                    Buka detail
                                </a>
                            @endif

                            @unless ($notification->is_read)
                                <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        Tandai dibaca
                                    </button>
                                </form>
                            @endunless
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="text-center py-5">
                <i class="bi bi-bell-slash fs-2 text-body-secondary" aria-hidden="true"></i>
                <h3 class="h6 mt-3">Belum ada notifikasi</h3>
                <p class="small text-body-secondary mb-0">
                    Notifikasi penugasan, pengingat deadline, dan aktivitas belajar akan muncul di sini.
                </p>
            </div>
        @endforelse

        @if ($notifications->hasPages())
            <div class="mt-3">{{ $notifications->links() }}</div>
        @endif
    </div>
@endsection
