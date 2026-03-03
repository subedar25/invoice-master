@extends('layouts.custom-admin')
@section('title', 'Notifications','bold')


@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Notifications</h3>

        @if(auth()->user()->unreadNotifications->count() > 0)
            <form action="{{ route('notifications.markAllRead') }}" method="POST">
                @csrf
                <button class="btn btn-sm btn-primary">Mark All as Read</button>
            </form>
        @endif
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if($notifications->count() > 0)
                <ul class="list-group list-group-flush">

                    @foreach($notifications as $notification)
                        @php $data = $notification->data; @endphp

                        <li class="list-group-item d-flex justify-content-between align-items-start 
                                   {{ is_null($notification->read_at) ? 'bg-light' : '' }}">

                            <div>
                                <strong>{{ $data['title'] ?? 'Notification' }}</strong>
                                <p class="mb-1 text-muted small">{{ $data['message'] ?? '' }}</p>
                                <small class="text-secondary">{{ $notification->created_at->diffForHumans() }}</small>
                            </div>

                            <div class="ml-3 text-end">
                                {{-- Mark Read --}}
                                @if(is_null($notification->read_at))
                                    <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success">Mark Read</button>
                                    </form>
                                @endif

                                {{-- Open Link --}}
                                @if(isset($data['url']))
                                    <a href="{{ $data['url'] }}" class="btn btn-sm btn-outline-primary mt-1">
                                        View
                                    </a>
                                @endif
                            </div>

                        </li>
                    @endforeach

                </ul>
            @else
                <div class="text-center py-5">
                    <p class="text-muted mb-0">No notifications available.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-3">
        {{ $notifications->links() }} 
    </div>

</div>
@endsection
