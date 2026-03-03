 <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="{{ route('masterapp.dashboard') }}" class="nav-link">Home</a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <!-- Messages Dropdown Menu -->
                <!-- <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="far fa-comments"></i>
                        <span class="badge badge-danger navbar-badge">3</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <a href="#" class="dropdown-item">
                           
                            <div class="media">
                               
                                <div class="media-body">
                                    <h3 class="dropdown-item-title">
                                        Brad Diesel
                                        <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
                                    </h3>
                                    <p class="text-sm">Call me whenever you can...</p>
                                </div>
                            </div>
                           
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
                    </div>
                </li> -->
                <!-- Notifications Dropdown Menu -->
                <!-- <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="far fa-bell"></i>
                        <span class="badge badge-warning navbar-badge">15</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <span class="dropdown-item-header">15 Notifications</span>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-envelope mr-2"></i> 4 new messages
                            <span class="float-right text-muted text-sm">3 mins</span>
                        </a>
                       
                    </div>
                </li> -->
                <!-- Fullscreen -->
                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>
            </ul>
<li class="nav-item dropdown">
    <a class="nav-link position-relative dropdown-toggle" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-bell" style="transition: all 0.2s ease; color: #ffc107; transform: scale(1.2);"></i>

        @php
            $unreadCount = auth()->user()->unreadNotifications()->count();
        @endphp

        @if($unreadCount > 0)
            <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </a>

    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown">
        <li><h6 class="dropdown-header">Notifications</h6></li>

        @php
            $notifications = auth()->user()->notifications()->latest()->take(5)->get();
        @endphp

        @forelse($notifications as $notification)
            <li>
                <a class="dropdown-item d-flex justify-content-between align-items-start"
                   href="{{ route('notifications.markAsRead', $notification->id) }}">
                    <div class="{{ $notification->read_at === null ? 'fw-bold' : '' }}">
                        {{ $notification->data['message'] ?? 'New Notification' }}<br>
                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                    </div>

                    @if(is_null($notification->read_at))
                        <span class="badge bg-primary rounded-pill">New</span>
                    @endif
                </a>
            </li>
        @empty
            <li><span class="dropdown-item text-muted">No notifications found</span></li>
        @endforelse

        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="{{ route('masterapp.notifications.index') }}">Show All</a></li>
    </ul>
</li>

            <!-- User Account Dropdown -->
            <li class="nav-item user-menu">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <!-- <img src="{{ asset('dist/img/user2-160x160.jpg') }}" class="user-image img-circle elevation-8" alt="User Image"> -->
                    <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <!-- User Image -->
                    <div class="user-card bg-primary">
                        <div class="card-body">
                            <h4 class="user-card-text">{{ Auth::user()->name }}</h4>
                            <p>{{ Auth::user()->email }}</p>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    
                    <a href="{{ route('profile.edit') }}" class="dropdown-item">
                        <i class="fas fa-user mr-2"></i> Profile
                    </a>
                     <a href="{{ route('profile.changepassword') }}" class="dropdown-item">
                        <i class="fas fa-key mr-2"></i> Change Password
                    </a>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-cog mr-2"></i> Settings
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('logout') }}" class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt mr-2"></i> Sign Out
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </li>
        </nav>