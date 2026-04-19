<div class="row">
    <!-- Left Menu -->
    <div class="col-md-3 col-lg-2 settings-menu">
        <ul class="nav flex-column">
             @can('department')
            <li class="nav-item">
                <a href="#" wire:click.prevent="setActive('department')"
                    class="nav-link {{ $active == 'department' ? 'active' : '' }}">Departments</a>
            </li>
             @endcan
             @can('list-organization')
            <li class="nav-item">
                <a href="#" wire:click.prevent="setActive('organization')"
                    class="nav-link {{ $active == 'organization' ? 'active' : '' }}">Organizations</a>
            </li>
             @endcan
             @can('country')
            <li class="nav-item">
                <a href="#" wire:click.prevent="setActive('country')"
                    class="nav-link {{ $active == 'country' ? 'active' : '' }}">Country</a>
            </li>
             @endcan
             @can('state')
            <li class="nav-item">
                <a href="#" wire:click.prevent="setActive('state')"
                    class="nav-link {{ $active == 'state' ? 'active' : '' }}">State</a>
            </li>
             @endcan
             @can('locations')
            <li class="nav-item">
                <a href="#" wire:click.prevent="setActive('location')"
                    class="nav-link {{ $active == 'location' ? 'active' : '' }}">Locations</a>
            </li>
             @endcan
             @can('vendors')
            <li class="nav-item">
                <a href="#" wire:click.prevent="setActive('vendor')"
                    class="nav-link {{ $active == 'vendor' ? 'active' : '' }}">Vendors</a>
            </li>
             @endcan
             @can('vendors')
            <li class="nav-item">
                <a href="#" wire:click.prevent="setActive('vendor-category')"
                    class="nav-link {{ $active == 'vendor-category' ? 'active' : '' }}">Vendor Category</a>
            </li>
             @endcan
             @can('outlets')
            <li class="nav-item">
                <a href="#" wire:click.prevent="setActive('outlet')"
                    class="nav-link {{ $active == 'outlet' ? 'active' : '' }}">Outlets</a>
            </li>
             @endcan
             @can('products')
            <li class="nav-item">
                <a href="#" wire:click.prevent="setActive('product')"
                    class="nav-link {{ $active == 'product' ? 'active' : '' }}">Products</a>
            </li>
             @endcan
             @can('taxes')
            <li class="nav-item">
                <a href="#" wire:click.prevent="setActive('tax')"
                    class="nav-link {{ $active == 'tax' ? 'active' : '' }}">Taxes</a>
            </li>
             @endcan
            @can('designation')
            <li class="nav-item">
                <a href="#" wire:click.prevent="setActive('designation')"
                    class="nav-link {{ $active == 'designation' ? 'active' : '' }}">Designation</a>
            </li>
            @endcan
        </ul>
    </div>

    <!-- Right Content -->
    <div class="col-md-9 col-lg-10 settings-content">
        @if($active == 'department')
            @livewire('master-app.masters.department')
        @elseif($active == 'organization')
            @livewire('master-app.masters.organization')
        @elseif($active == 'country')
            @livewire('master-app.masters.country')
        @elseif($active == 'state')
            @livewire('master-app.masters.state')
        @elseif($active == 'location')
            @livewire('master-app.masters.location')
        @elseif($active == 'vendor')
            @livewire('master-app.masters.vendor')
        @elseif($active == 'vendor-category')
            @livewire('master-app.masters.vendor-category')
        @elseif($active == 'outlet')
            @livewire('master-app.masters.outlet')
        @elseif($active == 'product')
            @livewire('master-app.masters.product')
        @elseif($active == 'tax')
            @livewire('master-app.masters.tax')
        @elseif($active == 'designation')
            @livewire('master-app.masters.designation')
        @endif
    </div>
</div>
