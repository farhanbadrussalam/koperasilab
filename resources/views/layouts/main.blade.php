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

    {{-- Plugin --}}
    <link href="{{ asset('assets/toast/toastr.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('assets/DataTables/DataTables-1.13.5/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet"/>

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Navbar -->
        @include('layouts.navbar')
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        @include('layouts.sidebar')


        <main>
            @yield('content')
        </main>

        @include('layouts.footer')
    </div>

    <script src="{{ asset('assets/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/inputmask/jquery.inputmask.min.js') }}"></script>
    <script src="{{ asset('assets/js/global.js') }}"></script>
    <script src="{{ asset('assets/js/adminlte.min.js') }}"></script>
    <script src="{{ asset('assets/DataTables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/toast/toastr.min.js') }}"></script>
    {{-- <script src="{{ asset('assets/DataTables/datatables.min.js') }}"></script> --}}
    <script src="{{ asset('assets/DataTables/DataTables-1.13.5/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/DataTables/DataTables-1.13.5/js/dataTables.bootstrap5.min.js') }}"></script>

    @stack('scripts')
</body>

</html>
