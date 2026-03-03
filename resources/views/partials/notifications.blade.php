{{-- @extends('layouts.custom-admin')

@section('title', 'Notifications','bold') --}}
@php
    $unreadCount = auth()->user()->unreadNotifications->count();
    $recent = auth()->user()->notifications()->latest()->take(10)->get();
@endphp

<div class="nav-notifications">
    <strong>
    <a href="#" id="notifBell" class="position-relative" onclick="toggleNotifDropdown(event)">
        <i class="fa fa-bell"></i>
        @if($unreadCount > 0)
            <span id="notifCount" class="badge badge-danger">{{ $unreadCount }}</span>
        @endif
    </a>
</strong>

    <div id="notifDropdown" class="notif-dropdown" style="display:none;">
        <div class="notif-header d-flex justify-content-between align-items-center">
            <strong>Notifications</strong>
            <a href="#" id="markAllRead">Mark all read</a>
        </div>

        <ul class="list-unstyled">
            @foreach($recent as $n)
                @php $data = $n->data; @endphp
                <li class="notif-item {{ is_null($n->read_at) ? 'unread' : '' }}" data-id="{{ $n->id }}">
                    <a href="{{ $data['url'] ?? '#' }}" class="notif-link" onclick="markRead(event, '{{ $n->id }}', '{{ $data['url'] ?? '#' }}')">
                        <div class="notif-title">{{ $data['title'] ?? 'Notification' }}</div>
                        <div class="notif-msg small">{{ $data['message'] ?? '' }}</div>
                        <div class="notif-time small text-muted">{{ $n->created_at->diffForHumans() }}</div>
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="notif-footer text-center">
            <a href="{{ route('notifications.index') }}">View all</a>
        </div>
    </div>
</div>

<style>
/* tiny styles — adapt to your CSS framework */
.notif-dropdown { width: 320px; background: #fff; border: 1px solid #ddd; position: absolute; right: 0; z-index: 1000; padding: 10px; border-radius: 4px; }
.notif-item.unread { background:#f6f8fb; }
.badge { position: absolute; top: -4px; right: -4px; }
</style>


<script>
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}

function toggleNotifDropdown(e) {
    e.preventDefault();
    const dd = document.getElementById('notifDropdown');
    dd.style.display = dd.style.display === 'none' ? 'block' : 'none';
}

// mark single notification read and navigate
function markRead(e, id, url) {
    e.preventDefault();

    fetch('/notifications/' + id + '/read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json'
        },
        body: JSON.stringify({})
    }).then(r => r.json())
      .then(data => {
          // update UI: decrement badge, mark item as read
          const el = document.querySelector('.notif-item[data-id="'+id+'"]');
          if (el) el.classList.remove('unread');
          const countEl = document.getElementById('notifCount');
          if (countEl) {
              let n = parseInt(countEl.textContent || '0') - 1;
              if (n <= 0) countEl.remove();
              else countEl.textContent = n;
          }
          // navigate
          if (url && url !== '#') window.location = url;
      }).catch(err => console.error(err));
}

// mark all read
document.addEventListener('click', function(e){
    if (e.target && e.target.id === 'markAllRead') {
        e.preventDefault();
        fetch('/notifications/mark-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({})
        }).then(r => r.json()).then(() => {
            document.querySelectorAll('.notif-item.unread').forEach(i => i.classList.remove('unread'));
            const countEl = document.getElementById('notifCount');
            if (countEl) countEl.remove();
        });
    }
});
</script>
