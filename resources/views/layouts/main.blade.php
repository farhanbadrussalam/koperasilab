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
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=Nunito">

    <!-- Theme adminLTE -->
    <link rel="stylesheet" href="{{ asset('assets/css/adminlte.min.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">

    {{-- Plugin --}}
    <link rel="stylesheet" href="{{ asset('assets/jquery/jquery-ui.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/toast/toastr.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/DataTables/DataTables-1.13.5/css/dataTables.bootstrap5.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/sweetalert2/sweetalert2.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/dropify/css/dropify.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/select2/css/theme-bootstrap-5/select2-bootstrap-5-theme.min.css') }}">
    @include('flatpickr::components.style')

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
    <script src="{{ asset('assets/jquery/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/inputmask/jquery.inputmask.min.js') }}"></script>
    <script src="{{ asset('assets/js/global.js') }}"></script>
    <script src="{{ asset('assets/js/adminlte.min.js') }}"></script>
    <script src="{{ asset('assets/DataTables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/toast/toastr.min.js') }}"></script>
    <script src="{{ asset('assets/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('assets/DataTables/DataTables-1.13.5/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/DataTables/DataTables-1.13.5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/dropify/js/dropify.js') }}"></script>
    <script src="{{ asset('vendor/select2/js/select2.full.js') }}"></script>
    @include('flatpickr::components.script')
    @stack('scripts')

    <script>
        @if (session('success'))
            toastr.success('{{ session('success') }}');
        @elseif (session('error'))
            toastr.error('{{ session('error') }}');
        @endif

        $(function () {
            let user = @json(Auth::user());

            loadNotifikasi();
            let chanel = window.Echo.private(`jadwal.${user.id}`).listen('.notif', (result) => {
                toastr.info(
                    `
                        <div>${result.data.type.toUpperCase()}</div>
                        <div>${result.message}</div>
                    `
                );
                loadNotifikasi();
            })
        })

        function loadNotifikasi() {
            $.ajax({
                url: "{{ url('api/getNotifikasi') }}",
                dataType: 'json',
                method: 'GET',
                processData: true,
                headers: {
                    'Authorization' : `Bearer {{ $token }}`,
                    'Content-Type': 'application/json'
                },
            }).done((result) => {
                let html = '';
                let countLonceng = 0;
                for (const notif of result.data) {
                    html += `
                        <div class="card shadow text-muted mb-1 ${notif.status==1 && 'bg-info-subtle'}" data-id="${notif.id}" role="button" onclick="notifGoTo(this, '${notif.type}')">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">${notif.message}</div>
                                    <div class="col-12 text-end">${dateFormat(notif.created_at)}</div>
                                </div>
                            </div>
                        </div>
                    `;
                    notif.status == 1 && countLonceng++;
                }
                if(countLonceng > 0){
                    $('#count_lonceng').show();
                    $('#count_lonceng').html(countLonceng);
                }
                if(result.data.length == 0){
                    html = `<div class="text-center">No data notifications</div>`;
                }
                $('#body-notif').html(html);
            })
        }

        function notifGoTo(obj, type){
            let notifId = $(obj).data('id');
            let url;
            type = type.toLowerCase();
            switch (type) {
                case 'jadwal':
                    url = "{{ route('jadwal.index') }}";
                    break;
                case 'permohonan':
                    url = "{{ route('permohonan.index') }}";
                    break;
                default:
                    break;
            }

            $.ajax({
                method: 'GET',
                url : '{{ url("api/setNotifikasi") }}',
                dataType: "json",
                processData: true,
                data: {
                    id: notifId,
                    status: 2
                },
                headers: {
                    'Authorization' : `Bearer {{ $token }}`,
                    'Content-Type': 'application/json'
                },
            }).done(result => {
                // console.log(result);
                window.location.href = url;
            })
        }
    </script>
</body>

</html>
