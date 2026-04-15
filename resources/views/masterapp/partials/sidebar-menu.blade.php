@php
    $from = request()->get('from');
@endphp


@auth
<!-- Dashboard -->
<li class="nav-item">
    <a href="{{ route('masterapp.dashboard') }}" class="nav-link {{ request()->routeIs('masterapp.dashboard') ? 'active' : '' }}">
        <i class="nav-icon fas fa-tachometer-alt"></i>
        <p>Dashboard</p>
    </a>
</li>
<!-- Invoices -->
@can('list-invoices')
<li class="nav-item">
    <a href="{{ route('invoice.index') }}" class="nav-link {{ request()->routeIs('invoice.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-file-invoice-dollar"></i>
        <p>Invoices</p>
    </a>
</li>
@endcan
<!-- Users -->
@canany(['list-users'])
<li class="nav-item has-treeview {{ request()->routeIs('masterapp.users.*', 'masterapp.entity.*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ request()->routeIs('masterapp.users.*', 'masterapp.entity.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-users-cog"></i>
        <p>
            Manage Users
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview nav-treeview-inset ml-2" style="padding-right: 1.25rem;">
        @can('list-users')
        <li class="nav-item">
            <a href="{{ route('masterapp.users.index') }}" class="nav-link {{ request()->routeIs('masterapp.users.*') || (request()->routeIs('masterapp.entity.*') && $from === 'users') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <p>All Users</p>
            </a>
        </li>
        @endcan
    </ul>
</li>
@endcanany
@can('list-timesheets')
<!-- <li class="nav-item">
    <a href="{{ route('masterapp.timesheets.index') }}" class="nav-link {{ request()->routeIs('masterapp.timesheets.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-user-clock"></i>
        <p>Manage Timesheets</p>
    </a>
</li> -->
@endcan
@can('list-time-off-requests')
<!-- <li class="nav-item">
    <a href="{{ route('masterapp.time-off-requests.index') }}" class="nav-link {{ request()->routeIs('masterapp.time-off-requests.*') ? 'active' : '' }}">
        {{-- <i class="nav-icon fas fa-calendar-times-o"></i> --}}
        <i class="nav-icon fas fa-calendar-times"></i>
        <p>Manage Time-off</p>
    </a>
</li> -->
@endcan



@can('list-role')
<li class="nav-item">
    <a href="{{ route('masterapp.roles.index') }}" class="nav-link {{ request()->routeIs('masterapp.roles.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-user-tag"></i>
        <p>Manage Role</p>
    </a>
</li>
@endcan

@can('list-modules')
<li class="nav-item">
    <a href="{{ route('masterapp.modules.index') }}" class="nav-link {{ request()->routeIs('masterapp.modules.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-layer-group"></i>
        <p>Manage Modules</p>
    </a>
</li>
@endcan

@can('list-permission')
<li class="nav-item">
    <a href="{{ route('masterapp.permissions.index') }}" class="nav-link {{ request()->routeIs('masterapp.permissions.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-key"></i>
        <p>Manage Permissions</p>
    </a>
</li>
@endcan
@can('list-master')
<li class="nav-item">
    <a href="{{ route('masterapp.masters') }}" class="nav-link {{ request()->routeIs('masterapp.masters') ? 'active' : '' }}">
        <i class="nav-icon fas fa-database"></i>
        <p>Masters</p>
    </a>
</li>
@endcan
@endauth

