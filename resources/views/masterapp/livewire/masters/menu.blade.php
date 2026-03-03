<div class="row">
    <!-- Left Menu -->
    <div class="col-md-3 col-lg-2 settings-menu">
        <ul class="nav flex-column">
            @can('organization_type')
            <li class="nav-item">
                <a href="#" wire:click.prevent="setActive('organization-type')"
                    class="nav-link {{ $active == 'organization-type' ? 'active' : '' }}">Organization Type</a>
            </li>
            @endcan
            @can('seasons')
            <li class="nav-item">
                <a href="#" wire:click.prevent="setActive('seasons')"
                    class="nav-link {{ $active == 'seasons' ? 'active' : '' }}">Seasons</a>
            </li>
            @endcan
            @can('publication')
            <li class="nav-item">
                <a href="#" wire:click.prevent="setActive('publication')"
                    class="nav-link {{ $active == 'publication' ? 'active' : '' }}">Publications</a>
            </li>
            @endcan
            @can('advertisers')
            <li class="nav-item">
                <a href="#" wire:click.prevent="setActive('advertisers')"
                    class="nav-link {{ $active == 'advertisers' ? 'active' : '' }}">Advertisers</a>
            </li>
            @endcan
        </ul>
    </div>

    <!-- Right Content -->
    <div class="col-md-9 col-lg-10 settings-content">
        @if($active == 'organization-type')
            @livewire('master-app.masters.organization-type')
        @elseif($active == 'seasons')
            @livewire('master-app.masters.seasons')
        @elseif($active == 'publication')
            @livewire('master-app.masters.publication')
        @elseif($active == 'advertisers')
            @livewire('master-app.masters.advertisers')
        @endif
    </div>
</div>