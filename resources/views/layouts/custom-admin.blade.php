<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="x-ua-compatible" content="ie=edge">
<meta name="csrf-token" content="{{ csrf_token() }}">
 
    <title>{{ config('app.name', 'Invoice Masters') }} - @yield('title')</title>
 
    <!-- Favicon -->
<link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.ico') }}">
 
    <!-- Font Awesome -->
<link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
 
    <!-- AdminLTE -->
<link rel="stylesheet" href="{{ asset('vendor/almasaeed2010/adminlte/dist/css/adminlte.min.css') }}">
 
    <!-- DataTables CSS (organization theme) -->
<link rel="stylesheet" href="{{ theme_asset('dataTables.bootstrap5.min.css') }}">
<link rel="stylesheet" href="{{ theme_asset('select.bootstrap5.min.css') }}">
<link rel="stylesheet" href="{{ theme_asset('buttons.bootstrap5.min.css') }}">
<link rel="stylesheet" href="{{ theme_asset('responsive.bootstrap5.min.css') }}">
 
    <!-- Custom Admin CSS -->
<link rel="stylesheet" href="{{ theme_asset('admin-custom.css') }}">
 
    <!-- Vite -->

    @vite(['resources/css/app.css', 'resources/js/app.js'])
 
    @stack('styles')
    <link rel="stylesheet" href="{{ theme_asset('bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ theme_asset('bootstrap5.min.css') }}">
    
</head>
 
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- ============================================================= -->
        <!-- PRELOADER - OPTIONAL -->
        <!-- ============================================================= -->
        <div class="preloader flex-column justify-content-center align-items-center">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- CUSTOM HEADER -->
        <!-- ============================================================= -->
         @include('masterapp.partials.top-menu')

        <!-- ============================================================= -->
        <!-- CUSTOM LEFT PANEL (SIDEBAR) -->
        <!-- ============================================================= -->
         @include('masterapp.partials.sidebar-panel')

        <!-- ============================================================= -->
        <!-- CONTENT WRAPPER -->
        <!-- ============================================================= -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
             @include('masterapp.partials.title-breadcrum')

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </section>
        </div>

        <!-- Footer -->
        <footer class="main-footer">
            <strong>Copyright &copy; {{ date('Y') }} <a href="#">{{ config('app.name') }}</a>.</strong>
            All rights reserved.
        </footer>
    </div>
<link rel="stylesheet" href="{{ theme_asset('quill.snow.css') }}">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>




    <!-- AdminLTE JS -->   
     <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>



         <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script src="{{ asset('vendor/almasaeed2010/adminlte/dist/js/adminlte.min.js') }}"></script>
     <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
     <script src="{{ asset('js/popper.min.js') }}"></script>
     <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/inline-edit.js') }}"></script>
    <script src="{{ asset('js/settings-panel.js') }}"></script>
    <script src="{{ asset('js/users-index.js') }}"></script>
    <script src="{{ asset('js/users-create.js') }}"></script>
    <script src="{{ asset('js/users-duplicate.js') }}"></script>
    <script src="{{ asset('js/users-edit.js') }}"></script>
    <script src="{{ asset('js/driver-index.js')}}"></script>
    <script src="{{ asset('js/driver-edit.js') }}"></script>
    {{-- <script src="{{ asset('js/users-index-inline-edit.js')}}"></script> --}}
    <script src="{{ asset('js/select-row-bgcolor.js') }}"></script>
    <script src="{{ asset('js/vehicles-index.js') }}"></script>
    <script src="{{ asset('js/vehicles-create.js') }}"></script>
    <script src="{{ asset('js/vehicles-edit.js') }}"></script>
    <script src="{{ asset('js/vehicles-duplicate.js') }}"></script>
    <script src="{{ asset('js/clients-index.js') }}"></script>
    <script src="{{ asset('js/clients-edit.js') }}"></script>
    <script src="{{ asset('js/clients-create.js') }}"></script>
    <script src="{{ asset('js/clients-duplicate.js') }}"></script>
    
    {{-- DataTables CSS   --}}


<script src="{{ asset('js/settings-panel.js') }}"></script>

<link rel="stylesheet" href="{{ theme_asset('admin-custom.css') }}">
    <!-- DataTables CSS -->
<link rel="stylesheet" href="{{ theme_asset('dataTables.bootstrap5.min.css') }}">
<link rel="stylesheet" href="{{ theme_asset('select.bootstrap5.min.css') }}">
<link rel="stylesheet" href="{{ theme_asset('buttons.bootstrap5.min.css') }}">
<link rel="stylesheet" href="{{ theme_asset('responsive.bootstrap5.min.css') }}">


<!-- Bootstrap JS (required for modals) -->
<script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/almasaeed2010/adminlte/dist/js/adminlte.min.js') }}"></script>
 {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> --}}

{{-- ================= PLUGINS ================= --}}
 
<!-- SweetAlert -->

<script src="{{ asset('js/sweetalert2.js') }}"></script>
 {{-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}
<!-- Quill -->
<link rel="stylesheet" href="{{ theme_asset('quill.snow.css') }}">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
 
{{-- ================= DATATABLES ================= --}}
 

<script src="{{ asset('js/sweetalert2.js') }}"></script>

{{-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}


<!-- DataTables Core -->

<script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('js/dataTables.select.min.js') }}"></script>
<script src="{{ asset('js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('js/responsive.bootstrap5.min.js') }}"></script>
 
<script src="{{ asset('js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('js/buttons.bootstrap5.min.js') }}"></script>
<script src="{{ asset('js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('js/buttons.print.min.js') }}"></script>
<script src="{{ asset('js/buttons.colVis.min.js') }}"></script>
 
<script src="{{ asset('js/jszip.min.js') }}"></script>
<script src="{{ asset('js/pdfmake.min.js') }}"></script>
<script src="{{ asset('js/vfs_fonts.js') }}"></script>
 
{{-- ================= GLOBAL JS ================= --}}
 
<script src="{{ asset('js/inline-edit.js') }}"></script>
<script src="{{ asset('js/settings-panel.js') }}"></script>
<script src="{{ asset('js/select-row-bgcolor.js') }}"></script>
<script src="{{ asset('js/column-toggle.js') }}"></script>
 
{{-- ================= MODULE JS ================= --}}
 
{{-- Users --}}
{{-- <script src="{{ asset('js/users-index.js') }}"></script> --}}
{{-- <script src="{{ asset('js/users-create.js') }}"></script>
<script src="{{ asset('js/users-edit.js') }}"></script>
<script src="{{ asset('js/users-duplicate.js') }}"></script> --}}
 
{{-- Drivers --}}
<script src="{{ asset('js/driver-index.js') }}"></script>
{{-- <script src="{{ asset('js/driver-edit.js') }}"></script> --}}
 
{{-- Vehicles --}}
<script src="{{ asset('js/vehicles-index.js') }}"></script>
{{-- <script src="{{ asset('js/vehicles-create.js') }}"></script> --}}
{{-- <script src="{{ asset('js/vehicles-edit.js') }}"></script> --}}
{{-- <script src="{{ asset('js/vehicles-duplicate.js') }}"></script> --}}
 
{{-- Clients --}}
<script src="{{ asset('js/clients-index.js') }}"></script>
{{-- <script src="{{ asset('js/clients-create.js') }}"></script> --}}
{{-- <script src="{{ asset('js/clients-edit.js') }}"></script> --}}
{{-- <script src="{{ asset('js/clients-duplicate.js') }}"></script> --}}
 
{{-- Vehicle Expenses --}}
<script src="{{ asset('js/vehicle-expenses-index.js') }}"></script>
{{-- Timesheets --}}
{{-- <script src="{{ asset('js/timesheets-index.js') }}"></script> --}}

@stack('scripts')
 
</body>
</html>
