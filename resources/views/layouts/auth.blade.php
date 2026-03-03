<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.ico') }}">
    <title>@yield('title', config('app.name', 'Pulse Publication'))</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <!-- AdminLTE / Bootstrap -->
    <link rel="stylesheet" href="{{ asset('vendor/almasaeed2010/adminlte/dist/css/adminlte.min.css') }}">
    @stack('styles')
</head>
<body class="hold-transition login-page">
    <div class="login-box" style="width: 480px;">
        <div class="card card-outline card-primary shadow">
            <div class="card-header text-center">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" style="max-height: 50px;">
                </a>
            </div>
            <div class="card-body">
                @yield('content')
            </div>
        </div>
    </div>
    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
