<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="{{ route('dashboard') }}" class="nav-link">Home</a>
      </li>
      <!-- <li class="nav-item d-none d-sm-inline-block">
        <a href="#" class="nav-link">Contact</a>
      </li> -->
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- Navbar Search -->
      <!-- <li class="nav-item">
        <a class="nav-link" data-widget="navbar-search" href="#" role="button">
          <i class="fas fa-search"></i>
        </a>
        <div class="navbar-search-block">
          <form class="form-inline">
            <div class="input-group input-group-sm">
              <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
              <div class="input-group-append">
                <button class="btn btn-navbar" type="submit">
                  <i class="fas fa-search"></i>
                </button>
                <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
          </form>
        </div>
      </li> -->

      <!-- Organization Switcher -->
      <li class="nav-item dropdown">
          <a class="nav-link" data-toggle="dropdown" href="#" title="Switch organization">
              <i class="fas fa-building mr-1"></i>
              <span class="d-none d-md-inline">
                  {{ $orgSwitcherCurrentOrganization->name ?? 'Select Organization' }}
              </span>
          </a>

          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
              <div class="dropdown-item dropdown-header">
                  Organization
              </div>
              <div class="dropdown-divider"></div>

              @php
                  $orgs = $orgSwitcherOrganizations ?? collect();
                  $currentOrgId = (int) ($orgSwitcherCurrentOrganization->id ?? 0);
              @endphp

              @if ($orgs->isEmpty())
                  <span class="dropdown-item text-muted">No organizations available</span>
              @else
                  @foreach ($orgs as $org)
                      <form method="POST" action="{{ route('masterapp.organization.switch') }}" class="m-0">
                          @csrf
                          <input type="hidden" name="organization_id" value="{{ $org->id }}">
                          <button type="submit" class="dropdown-item d-flex align-items-center justify-content-between">
                              <span class="text-truncate" style="max-width: 220px;">{{ $org->name }}</span>
                              @if ((int) $org->id === $currentOrgId)
                                  <i class="fas fa-check text-success"></i>
                              @endif
                          </button>
                      </form>
                  @endforeach
              @endif
          </div>
      </li>

      <!-- Notifications Dropdown Menu -->
       @php
            // Distinct names: avoid overwriting $notifications / $unreadCount from child views (e.g. notifications index).
            $isSystemUser = (auth()->user()?->user_type ?? '') === 'systemuser';
            if ($isSystemUser) {
                $topMenuNotificationsQuery = \App\Models\Notification::query()
                    ->where('notifiable_type', \App\Models\User::class)
                    ->with(['notifiable:id,first_name,last_name,email']);
                $topMenuUnreadCount = auth()->user()->notifications()->whereNull('read_at')->count();
                $topMenuNotifications = (clone $topMenuNotificationsQuery)->latest()->take(5)->get();
            } else {
                $currentOrgId = (int) session('current_organization_id', 0);
                $topMenuNotificationsQuery = auth()->user()->notifications()
                    ->when($currentOrgId > 0, function ($q) use ($currentOrgId) {
                        $q->where('organization_id', $currentOrgId);
                    });
                $topMenuUnreadCount = (clone $topMenuNotificationsQuery)->whereNull('read_at')->count();
                $topMenuNotifications = (clone $topMenuNotificationsQuery)->latest()->take(5)->get();
            }
        @endphp

        <li class="nav-item dropdown notif-hover-dropdown">
    <a class="nav-link" data-toggle="dropdown" href="#">
        <i class="far fa-bell"></i>

        @if ($topMenuUnreadCount > 0)
            <span id="topMenuNotifCount" class="badge badge-warning navbar-badge">{{ $topMenuUnreadCount }}</span>
        @endif
    </a>

    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" id="topMenuNotifDropdown">
        <div class="dropdown-item dropdown-header d-flex justify-content-between align-items-center flex-wrap">
            <span>{{ $topMenuUnreadCount }} Notifications</span>
            @if ($topMenuUnreadCount > 0)
                <a href="#" id="topMenuMarkAllRead" class="ml-2 text-primary small">Mark All as Read</a>
            @endif
        </div>

        @forelse ($topMenuNotifications as $notification)
            <div class="dropdown-divider"></div>

            <a href="{{ route('masterapp.notifications.read', $notification->id) }}"
               class="dropdown-item js-topmenu-notif-item {{ is_null($notification->read_at) ? 'font-weight-bold' : '' }}"
               data-id="{{ $notification->id }}">
                <i class="fas fa-bell mr-2"></i>
                {{ $notification->data['message'] ?? 'Notification' }}
                @if($isSystemUser && $notification->relationLoaded('notifiable'))
                    <span class="d-block small text-muted">{{ $notification->notifiable?->name ?? '' }}</span>
                @endif
                <span class="float-right text-muted text-sm">
                    {{ $notification->created_at->diffForHumans() }}
                </span>
            </a>

        @empty
            <div class="dropdown-divider"></div>
            <span class="dropdown-item text-muted">No notifications found</span>
        @endforelse

        <div class="dropdown-divider"></div>

        <a href="{{ route('masterapp.notifications.index') }}"
           class="dropdown-item dropdown-footer">
            View All
        </a>
    </div>
</li>

<li class="nav-item">
    <a class="nav-link" data-widget="fullscreen" href="#" role="button">
        <i class="fas fa-expand-arrows-alt"></i>
    </a>
</li>


        <!-- User Account -->
        <li class="nav-item dropdown user-menu">
            <a class="nav-link" data-toggle="dropdown" href="#">
              <i class="fas fa-user-circle user-icon"></i> <span class="user-name"> {{ auth()->user()->first_name }}</span>
            </a>

            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <div class="user-card bg-primary">
                    <div class="card-body text-center">
                        <h5>{{ Auth::user()->first_name }}</h5>
                        <p class="mb-0">{{ Auth::user()->email }}</p>
                    </div>
                </div>

                <div class="dropdown-divider"></div>

                <a href="{{ route('masterapp.settings') }}" class="dropdown-item">
                    <i class="fas fa-cog mr-2"></i> Settings
                </a>

                @can('list-auditlog')
                <a href="{{ route('masterapp.audit.index') }}" class="dropdown-item">
                    <i class="fas fa-clipboard-list mr-2"></i> Audit Logs
                </a>
                @endcan

                <div class="dropdown-divider"></div>

                <a href="{{ route('logout') }}"
                   class="dropdown-item"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt mr-2"></i> Sign Out
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                    @csrf
                </form>
            </div>
        </li>
    </ul>
  </nav>

@push('scripts')
<script>
(function () {
    var markAllRead = document.getElementById('topMenuMarkAllRead');
    if (!markAllRead) return;
    var csrf = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrf ? csrf.getAttribute('content') : '';
    var readAllUrl = {!! json_encode(route('masterapp.notifications.read-all')) !!};

    markAllRead.addEventListener('click', function (e) {
        e.preventDefault();
        if (!csrfToken) return;

        fetch(readAllUrl, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(function (res) {
            if (!res.ok) return;
            document.querySelectorAll('.js-topmenu-notif-item').forEach(function (el) {
                el.classList.remove('font-weight-bold');
            });
            var badge = document.getElementById('topMenuNotifCount');
            if (badge) badge.remove();
            var header = markAllRead.closest('.dropdown-header');
            if (header) {
                var countSpan = header.querySelector('span');
                if (countSpan) countSpan.textContent = '0 Notifications';
                markAllRead.remove();
            }
        }).catch(function () {});
    });
})();
</script>
@endpush

@push('styles')
<style>
    .notif-hover-dropdown:hover .dropdown-menu {
        display: block;
        margin-top: 0;
    }
</style>
@endpush
