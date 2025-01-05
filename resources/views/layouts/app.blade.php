<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Theme adminLTE -->
    <link rel="stylesheet" href="{{ asset('assets/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/jquery/jquery-ui.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/select2/css/theme-bootstrap-5/select2-bootstrap-5-theme.css') }}">

    <script src="{{ asset('assets/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/jquery/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/inputmask/jquery.inputmask.min.js') }}"></script>

    {{-- Magnific Popup --}}
    <link rel="stylesheet" href="{{ asset('vendor/magnific/magnific-popup.css') }}">
    <script src="{{ asset('vendor/magnific/jquery.magnific-popup.min.js') }}"></script>

    {{-- Select 2 --}}
    <script src="{{ asset('vendor/select2/js/select2.full.js') }}"></script>

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body>
    <input type="hidden" name="csrf" id="csrf-token" value="{{ csrf_token() }}">
    <input type="hidden" id="base_url" value="{{ url('') }}">

    <div id="app">
        <main>
            @yield('content')
        </main>
    </div>

    <script src="{{ asset('assets/js/global.js') }}"></script>
    @stack('scripts')
</body>
</html>
