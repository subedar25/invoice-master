<aside class="main-sidebar sidebar-dark-primary elevation-4">
    @php
        $selectedOrgName = trim((string) ($orgSwitcherCurrentOrganization->name ?? 'Select Organization'));
        $orgLogoPath = trim((string) ($orgSwitcherCurrentOrganization->logo ?? ''));
        if (in_array(strtolower(ltrim($orgLogoPath, '/')), ['images/logo.png', 'images/logo.jpg', 'images/logo.jpeg', 'images/logo.webp'], true)) {
            $orgLogoPath = '';
        }
        $orgLogoUrl = $orgLogoPath !== ''
            ? (\Illuminate\Support\Str::startsWith($orgLogoPath, ['http://', 'https://']) ? $orgLogoPath : asset(ltrim($orgLogoPath, '/')))
            : null;
        $nameParts = preg_split('/\s+/', $selectedOrgName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $brandInitials = collect($nameParts)
            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
            ->implode('');
        $brandInitials = $brandInitials !== '' ? $brandInitials : 'NA';
    @endphp

    <!-- Brand Logo -->
    <a href="/master-app/dashboard" class="brand-link d-flex align-items-center" title="{{ $selectedOrgName }}">
        @if ($orgLogoUrl)
            <img
                src="{{ $orgLogoUrl }}"
                alt="{{ $selectedOrgName }} Logo"
                class="brand-image organization-brand-logo elevation-2"
            >
        @else
            <span
                class="brand-image organization-brand-logo organization-brand-initials elevation-2 d-inline-flex align-items-center justify-content-center text-white font-weight-bold"
            >
                {{ $brandInitials }}
            </span>
        @endif
        <span class="brand-text font-weight-light text-truncate">{{ $selectedOrgName }}</span>
    </a>

            <!-- Sidebar -->
            <div class="sidebar">

                <!-- SidebarSearch Form -->
                <div class="form-inline">
                    <div class="input-group" data-widget="sidebar-search" data-min-length="1">
                        <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
                        <div class="input-group-append">
                            <button class="btn btn-sidebar">
                                <i class="fas fa-search fa-fw"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <!-- Dynamically include your menu items here -->
                        @include('masterapp.partials.sidebar-menu')
                    </ul>
                </nav>
            </div>
</aside>
