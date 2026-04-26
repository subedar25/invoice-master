<div class="row">
    <!-- Left Menu -->
    <div class="col-md-3 col-lg-2 settings-menu">
        <ul class="nav flex-column">
             @can('department')
            <li class="nav-item">
                <button type="button" wire:click="setActive('department')" wire:key="menu-department"
                    class="nav-link text-left w-100 bg-transparent border-0 {{ $active == 'department' ? 'active' : '' }}">Departments</button>
            </li>
             @endcan
             @can('list-organization')
             @if((auth()->user()?->user_type ?? '') === 'systemuser')
            <li class="nav-item">
                <button type="button" wire:click="setActive('organization')" wire:key="menu-organization"
                    class="nav-link text-left w-100 bg-transparent border-0 {{ $active == 'organization' ? 'active' : '' }}">Organizations</button>
            </li>
             @endif
             @endcan
             @can('country')
            <li class="nav-item">
                <button type="button" wire:click="setActive('country')" wire:key="menu-country"
                    class="nav-link text-left w-100 bg-transparent border-0 {{ $active == 'country' ? 'active' : '' }}">Country</button>
            </li>
             @endcan
             @can('state')
            <li class="nav-item">
                <button type="button" wire:click="setActive('state')" wire:key="menu-state"
                    class="nav-link text-left w-100 bg-transparent border-0 {{ $active == 'state' ? 'active' : '' }}">State</button>
            </li>
             @endcan
             @can('locations')
            <li class="nav-item">
                <button type="button" wire:click="setActive('location')" wire:key="menu-location"
                    class="nav-link text-left w-100 bg-transparent border-0 {{ $active == 'location' ? 'active' : '' }}">Locations</button>
            </li>
             @endcan
             @can('vendors')
            <li class="nav-item">
                <button type="button" wire:click="setActive('vendor')" wire:key="menu-vendor"
                    class="nav-link text-left w-100 bg-transparent border-0 {{ $active == 'vendor' ? 'active' : '' }}">Vendors</button>
            </li>
             @endcan
             @can('vendors')
            <li class="nav-item">
                <button type="button" wire:click="setActive('vendor-category')" wire:key="menu-vendor-category"
                    class="nav-link text-left w-100 bg-transparent border-0 {{ $active == 'vendor-category' ? 'active' : '' }}">Vendor Category</button>
            </li>
             @endcan
             @can('outlets')
            <li class="nav-item">
                <button type="button" wire:click="setActive('outlet')" wire:key="menu-outlet"
                    class="nav-link text-left w-100 bg-transparent border-0 {{ $active == 'outlet' ? 'active' : '' }}">Outlets</button>
            </li>
             @endcan
             @can('products')
            <li class="nav-item">
                <button type="button" wire:click="setActive('product')" wire:key="menu-product"
                    class="nav-link text-left w-100 bg-transparent border-0 {{ $active == 'product' ? 'active' : '' }}">Products</button>
            </li>
             @endcan
             @can('taxes')
            <li class="nav-item">
                <button type="button" wire:click="setActive('tax')" wire:key="menu-tax"
                    class="nav-link text-left w-100 bg-transparent border-0 {{ $active == 'tax' ? 'active' : '' }}">Taxes</button>
            </li>
             @endcan
            @can('designation')
            <li class="nav-item">
                <button type="button" wire:click="setActive('designation')" wire:key="menu-designation"
                    class="nav-link text-left w-100 bg-transparent border-0 {{ $active == 'designation' ? 'active' : '' }}">Designation</button>
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
