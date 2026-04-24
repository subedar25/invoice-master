<div class="row">
    <!-- Left Menu -->
    <div class="col-md-3 col-lg-2 settings-menu">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="#" wire:click.prevent="setActive('profile')"
                   class="nav-link {{ $active == 'profile' ? 'active' : '' }}">Profile</a>
            </li>
            <li class="nav-item">
                <a href="#" wire:click.prevent="setActive('password')"
                   class="nav-link {{ $active == 'password' ? 'active' : '' }}">Change Password</a>
            </li>
            @can('enable-two-factor')
            <li class="nav-item">
                <a href="#" wire:click.prevent="setActive('two-factor')" 
                   class="nav-link {{ $active == 'two-factor' ? 'active' : '' }}">Two-Factor Authentication</a>
            </li>
            @endcan
        </ul>
    </div>

    <!-- Right Content -->
    <div class="col-md-9 col-lg-10 settings-content">
        @if($active == 'profile')
            @livewire('master-app.settings.profile')
        @elseif($active == 'password')
            @livewire('master-app.settings.password')
        @elseif($active == 'two-factor')
            @livewire('master-app.settings.two-factor')
        @endif
    </div>
</div>

