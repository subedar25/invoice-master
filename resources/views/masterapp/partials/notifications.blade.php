{{-- @extends('layouts.custom-admin')

@section('title', 'Notifications','bold') --}}
{{-- @php
    $unreadCount = auth()->user()->unreadNotifications->count();
    $recent = auth()->user()->notifications()->latest()->take(10)->get();
@endphp --}}

@if(auth()->check())
<div class="nav-notifications position-relative">

    <a href="#" id="notifBell" class="position-relative">
        <i class="fa fa-bell"></i>

        @if($unreadCount > 0)
            <span id="notifCount" class="badge badge-danger notif-count-badge">{{ $unreadCount }}</span>
        @endif
    </a>

    <div id="notifDropdown"
         class="notif-dropdown"
         style="display:none;">

        <div class="notif-header d-flex justify-content-between align-items-center">
            <strong>Notifications</strong>
            <a href="#" id="markAllRead">Mark all read</a>
        </div>

        <ul class="list-unstyled mb-0">
            @forelse($recentNotifications as $n)
                @php $data = $n->data ?? []; @endphp

                {{-- <li class="notif-item {{ is_null($n->read_at) ? 'unread' : '' }}"
                    data-id="{{ $n->id }}"
                    data-url="{{ $data['url'] ?? '' }}">

                    <div class="notif-link px-2 py-2">
                        <div class="notif-title font-weight-bold">
                            {{ $data['title'] ?? 'Notification' }}
                        </div>
                        <div class="notif-msg small" style="word-wrap: break-word; max-width: 300px;">
                            {{ $data['message'] ?? '' }}
                        </div>
                        <div class="notif-time small text-muted">
                            {{ $n->created_at->diffForHumans() }}
                        </div>
                </div>
                </li> --}}
                   <li class="notif-item {{ is_null($n->read_at) ? 'unread' : '' }}"
                        data-id="{{ $n->id }}"
                         data-url="{{ $data['url'] ?? '' }}">
                             <div class="notif-link">
                                 <div class="notif-content">
                                   <div class="notif-title">
                                    {{ $data['title'] ?? 'Notification' }}
                                    </div>
                                     <div class="notif-msg">
                                          {{ $data['message'] ?? '' }}
                                       <div class="notif-time">
                                        {{ $n->created_at->diffForHumans() }}
                                         </div>
                                     </div>
                                 </div>
                    </li>
            @empty
                <li class="text-muted text-center py-3">
                    No notifications
                </li>
            @endforelse
        </ul>

        <div class="notif-footer text-center">
            <a href="{{ route('masterapp.notifications.index') }}">
                View all
            </a>
        </div>
    </div>
</div>
@endif



@push('styles')
<style>
.notif-dropdown {
    width: 400px;
    background: #fff;
    border: 1px solid #ddd;
    position: absolute;
    right: 0;
    z-index: 1000;
    padding: 10px;
    border-radius: 4px;
}
.notif-item.unread {
    background: #f6f8fb;
}
.badge {
    position: absolute;
    top: -4px;
    right: -4px;
}
.notif-count-badge {
    font-weight: bold;
}

.notif-msg {
    white-space: normal;
    overflow-wrap: anywhere;
    word-break: break-word;
}
</style>
@endpush



@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    const bell = document.getElementById('notifBell');
    const dropdown = document.getElementById('notifDropdown');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    if (!bell || !dropdown || !csrf) return;

    bell.addEventListener('click', e => {
        e.preventDefault();
        dropdown.style.display =
            dropdown.style.display === 'none' ? 'block' : 'none';
    });

    function updateBadgeCount(newCount) {
        const badge = document.getElementById('notifCount');
        if (newCount <= 0) {
            badge?.remove();
            return;
        }
        if (badge) {
            badge.textContent = newCount;
            badge.classList.add('notif-count-badge');
        } else {
            const span = document.createElement('span');
            span.id = 'notifCount';
            span.className = 'badge badge-danger notif-count-badge';
            span.textContent = newCount;
            bell.appendChild(span);
        }
    }

    // mark single notification as read
    document.querySelectorAll('.notif-item').forEach(item => {
        item.addEventListener('click', (e) => {
            if (item.classList.contains('unread')) {
                e.preventDefault();
                const id = item.dataset.id;
                const url = item.dataset.url;

                fetch(`{{ route('masterapp.notifications.read', ':id') }}`.replace(':id', id), {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    }
                }).then(() => {
                    item.classList.remove('unread');
                    const badge = document.getElementById('notifCount');
                    const current = badge ? parseInt(badge.textContent, 10) : 0;
                    updateBadgeCount(current - 1);
                    if (url) window.location.href = url;
                });
            } else if (item.dataset.url) {
                window.location.href = item.dataset.url;
            }
        });
    });

    // mark all as read
    document.getElementById('markAllRead')?.addEventListener('click', e => {
        e.preventDefault();

        fetch(`{{ route('masterapp.notifications.read-all') }}`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            }
        }).then(() => {
            document.querySelectorAll('.notif-item').forEach(i => i.classList.remove('unread'));
            updateBadgeCount(0);
        });
    });
});
</script>
@endpush
