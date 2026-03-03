@extends('masterapp.layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Notifications</h3>

        @if(auth()->user()->unreadNotifications->count() > 0)
            <button class="btn btn-sm btn-primary mark-all-read-btn">
                Mark All as Read
            </button>
        @endif
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">

            @if($notifications->count() > 0)
                <ul class="list-group list-group-flush">

                    @foreach($notifications as $notification)
                        @php $data = $notification->data ?? []; @endphp

                        <li class="list-group-item d-flex justify-content-between align-items-start
                            {{ is_null($notification->read_at) ? 'bg-light' : '' }}">

                            <div>
                                <strong>
                                    {{ $data['title'] ?? 'Notification' }}
                                </strong>

                                <p class="mb-1 text-muted small">
                                    {{ $data['message'] ?? '' }}
                                </p>

                                <small class="text-secondary">
                                    {{ $notification->created_at->diffForHumans() }}
                                </small>
                            </div>

                            <div class="ml-3 text-right">
                                {{-- Mark Read --}}
                                @if(is_null($notification->read_at))
                                    <button class="btn btn-sm btn-outline-success mark-read-btn"
                                            data-id="{{ $notification->id }}">
                                        Mark Read
                                    </button>
                                @endif

                                {{-- Open Link --}}
                                {{-- @if(!empty($data['url']))
                                    <a href="{{ $data['url'] }}"
                                       class="btn btn-sm btn-outline-primary mt-1">
                                        View
                                    </a>
                                @endif --}}
                            </div>

                        </li>
                    @endforeach

                </ul>
            @else
                <div class="text-center py-5">
                    <p class="text-muted mb-0">
                        No notifications available.
                    </p>
                </div>
            @endif

        </div>
    </div>

    {{-- <div class="mt-3 d-flex justify-content-top-end">
        {{ $notifications->links() }}
    </div> --}}
    @if ($notifications->count())
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted">
            Showing
            {{ $notifications->firstItem() }}
            to
            {{ $notifications->lastItem() }}
            of
            {{ $notifications->total() }}
            entries
        </div>

        <div class="pagination-links">
            {{ $notifications->links() }}
        </div>
    </div>
@endif

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Mark single notification as read
    document.querySelectorAll('.mark-read-btn').forEach(button => {
        button.addEventListener('click', function() {
            const notificationId = this.getAttribute('data-id');
            const listItem = this.closest('li');

            fetch(`{{ route('masterapp.notifications.read', ':id') }}`.replace(':id', notificationId), {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove background color and hide the button
                    listItem.classList.remove('bg-light');
                    this.style.display = 'none';

                    // Update notification count on bell icon in top menu
                    const topMenuCount = document.getElementById('topMenuNotifCount');
                    if (topMenuCount) {
                        const currentCount = parseInt(topMenuCount.innerText) || 0;
                        const newCount = currentCount - 1;
                        if (newCount <= 0) {
                            topMenuCount.remove();
                        } else {
                            topMenuCount.innerText = newCount;
                        }
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });

    // Mark all notifications as read
    const markAllBtn = document.querySelector('.mark-all-read-btn');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            fetch(`{{ route('masterapp.notifications.read-all') }}`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove background color from all unread items and hide buttons
                    document.querySelectorAll('li.bg-light').forEach(item => {
                        item.classList.remove('bg-light');
                    });
                    document.querySelectorAll('.mark-read-btn').forEach(btn => {
                        btn.style.display = 'none';
                    });
                    // Hide the "Mark All as Read" button
                    markAllBtn.style.display = 'none';

                    // Update notification count on bell icon to 0 (both in notifications page and top menu)
                    const notifCount = document.getElementById('notifCount');
                    if (notifCount) {
                        notifCount.remove();
                    }
                    const topMenuCount = document.getElementById('topMenuNotifCount');
                    if (topMenuCount) {
                        topMenuCount.remove();
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }
});

</script>
@endpush
