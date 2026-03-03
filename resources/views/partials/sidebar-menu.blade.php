@auth
<!-- Dashboard -->
<li class="nav-item">
    <a href="{{ route('dashboard') }}" class="nav-link">
        <i class="nav-icon fas fa-tachometer-alt"></i>
        <p>Dashboard</p>
    </a>
</li>

<!-- 
     @can('list-client')
     <li class="nav-item">
            <a href="{{ route('masterapp.contacts.index') }}" class="nav-link">
                <i class="fa fa-users fa-fw"></i> 
                <p>Manage Clients</p>
            </a>
        </li>
 @endcan
    -->




<!-- Users -->
@can('list-users')
<li class="nav-item">
    <a href="{{ route('masterapp.users.index') }}" class="nav-link">
        <i class="nav-icon fas fa-users"></i>
        <p>Manage Users</p>
    </a>
</li>
@endcan


<!-- 


@can('list-dropoint')
<li class="nav-item">
    <a href="{{ route('masterapp.droppoints.index') }}" class="nav-link">
        <i class="nav-icon fas fa-truck"></i>
        <p>Manage Drop Points</p>
    </a>
</li>
@endcan -->

@can('list-role')
<li class="nav-item">
    <a href="{{ route('masterapp.roles.index') }}" class="nav-link">
        <i class="nav-icon fas fa-user-tag"></i>
        <p>Manage Role</p>
    </a>
</li>
@endcan

@can('list-modules')
<li class="nav-item">
    <a href="{{ route('masterapp.modules.index') }}" class="nav-link">
        <i class="nav-icon fas fa-layer-group"></i>
        <p>Manage Modules</p>
    </a>
</li>
@endcan

@can('list-permission')
<li class="nav-item">
    <a href="{{ route('masterapp.permissions.index') }}" class="nav-link">
        <i class="nav-icon fas fa-key"></i>
        <p>Manage Permissions</p>
    </a>
</li>
@endcan
@endauth 
