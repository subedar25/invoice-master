<aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
    <a href="/master-app/dashboard" class="brand-link" title="Pulse Publication">
        {{-- <img src="../../dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8"> --}}
                        <img src="{{ asset('images/logo.png') }}" alt="My App Logo" class="brand-image img-circle elevation-3" style="opacity: .8">

        <span class="brand-text font-weight-light" style="font-size: 1.25rem;"><strong>Pulse Publication</strong></span>
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
