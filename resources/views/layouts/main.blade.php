<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="realtime" content="{{ Auth::check() && Auth::user()->realtime_notifications ? '1' : '0' }}">
    <meta name="auth-id" content="{{ Auth::id() }}">

    <title>{{ config('app.name', 'Laravel') }}{{ Auth::check() && count(Auth::user()->getRoleNames()) != 0 ? ' | ' . Auth::user()->getRoleNames()[0] : '' }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=Nunito">
    <link rel="stylesheet" href="{{ asset_versioned('assets/font/allFont.css') }}">

    <!-- Theme adminLTE -->
    <link rel="stylesheet" href="{{ asset_versioned('assets/css/styles.min.css') }}">
    <link rel="stylesheet" href="{{ asset_versioned('assets/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset_versioned('assets/css/main.css') }}">

    {{-- Plugin --}}
    <link rel="stylesheet" href="{{ asset_versioned('assets/jquery/jquery-ui.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset_versioned('assets/DataTables/DataTables-1.13.5/css/dataTables.bootstrap5.min.css') }}" />
    <link rel="stylesheet" href="{{ asset_versioned('assets/sweetalert2/sweetalert2.min.css') }}" />
    <link rel="stylesheet" href="{{ asset_versioned('assets/dropify/css/dropify.css') }}">
    <link rel="stylesheet" href="{{ asset_versioned('vendor/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset_versioned('vendor/select2/css/theme-bootstrap-5/select2-bootstrap-5-theme.css') }}">
    <link rel="stylesheet" href="{{ asset_versioned('vendor/flatpickr/flatpickr.min.css') }}">

    <!-- Scripts -->
    <script src="{{ asset_versioned('assets/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset_versioned('assets/jquery/jquery-ui.min.js') }}"></script>
    <script src="{{ asset_versioned('assets/inputmask/jquery.inputmask.min.js') }}"></script>
    <script src="{{ asset_versioned('assets/DataTables/datatables.min.js') }}"></script>
    <script src="{{ asset_versioned('assets/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset_versioned('assets/DataTables/DataTables-1.13.5/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset_versioned('assets/DataTables/DataTables-1.13.5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset_versioned('assets/dropify/js/dropify.js') }}"></script>
    <script src="{{ asset_versioned('assets/js/app.min.js') }}"></script>
    {{-- Select 2 --}}
    <script src="{{ asset_versioned('vendor/select2/js/select2.full.js') }}"></script>
    {{-- Flat pickr --}}
    <script src="{{ asset_versioned('vendor/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset_versioned('vendor/flatpickr/l10n/id.js') }}"></script>
    {{-- Signature --}}
    <script src="{{ asset_versioned('vendor/signature/signature_pad.umd.min.js') }}"></script>
    {{-- Magnific Popup --}}
    <link rel="stylesheet" href="{{ asset_versioned('vendor/magnific/magnific-popup.css') }}">
    <script src="{{ asset_versioned('vendor/magnific/jquery.magnific-popup.min.js') }}"></script>
    {{-- Parsley --}}
    <script src="{{ asset_versioned('vendor/parsley/parsley.min.js') }}"></script>
    <script src="{{ asset_versioned('vendor/parsley/setting.js') }}"></script>
    {{-- CKEditor --}}
    <script src="{{ asset_versioned('vendor/ckeditor/build/ckeditor.js') }}"></script>

    {{-- PeriodeJs --}}
    <script src="{{ asset_versioned('js/validation.js') }}"></script>
    <script src="{{ asset_versioned('js/SignatureSelect.js') }}"></script>
    <script src="{{ asset_versioned('js/periode.js') }}"></script>
    <script src="{{ asset_versioned('js/invoice.js') }}"></script>
    <script src="{{ asset_versioned('js/detail.js') }}"></script>
    <script src="{{ asset_versioned('js/upload.js') }}"></script>
    <script src="{{ asset_versioned('js/document.js') }}"></script>
    <script src="{{ asset_versioned('js/timeline.js') }}"></script>
    <script src="{{ asset_versioned('js/filter.js') }}"></script>
    <script src="{{ asset_versioned('js/cardList.js') }}"></script>
    <script src="{{ asset_versioned('js/inventory_tld.js') }}"></script>
    <script src="{{ asset_versioned('js/notifikasi.js') }}"></script>
    <script src="{{ asset_versioned('js/component/AdendumInformasi.js') }}"></script>

    {{-- Logo Aplikasi --}}
    <link rel="icon" type="image/png" href="{{ asset_versioned('images/favicon.png') }}">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>


<body>

    <!--  Body Wrapper -->
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">
        @auth
            <!-- Main Sidebar Container -->
            @include('layouts.sidebar')
        @endauth

        <!--  Main wrapper -->
        <div class="body-wrapper">
            @auth
                <header class="app-header shadow-sm">
                    <!-- Navbar -->
                    @include('layouts.navbar')

                </header>
            @endauth

            <div class="container-fluid">
                @yield('content')
            </div>
        </div>


        <!-- @include('layouts.footer') -->
    </div>

    {{-- modal --}}
    <x-modal.preview-ktp />
    <x-modal.quick-track />

    <script>
        const bearer = "{{ generateToken() }}";
        const csrf = "{{ csrf_token() }}";
        const base_url = "{{ url('') }}";
        const userActive = @json(Auth::user());
        const role = @json(Auth::check() ? Auth::user()->getRoleNames() : []);
        const permission = @json(Auth::check() ? Auth::user()->getDirectPermissions() : []);
        const permissionInRole = @json(Auth::check() ? Auth::user()->getPermissionsViaRoles() : []);
        const envirotment = "{{ config('app.env') }}";
        const statusUser = @json(Auth::check() ? Auth::user()->status : null);
    </script>
    <script src="{{ asset_versioned('assets/js/global.js') }}"></script>
    @stack('scripts')

    <script>
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 1500
            })
        @elseif (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: '{{ session('error') }}',
                showConfirmButton: false,
                timer: 1500
            })
        @elseif (session('warning'))
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: '{{ session('warning') }}',
                showConfirmButton: true
            })
        @endif
        // initialize config notifikasi
        window.__APP_ECHO_CONFIG = {
            broadcaster: "pusher",
            key: "{{ config('broadcasting.connections.pusher.key') }}",
            cluster: "{{ config('broadcasting.connections.pusher.options.cluster') }}",
            host: "{{ env('PUSHER_HOST', '') }}",
            wsPort: "{{ config('broadcasting.connections.pusher.options.port') }}",
            wssPort: "{{ config('broadcasting.connections.pusher.options.port') }}",
            forceTLS: {{ config('broadcasting.connections.pusher.options.useTLS') ? 'true' : 'false' }},
        };
        $(function() {
            let user = @json(Auth::user());
            if (user) {
                let notifikasi = new WidgetNotifikasi('container-notifikasi', {
                    'channel': `App.Models.User.${user.id}`,
                    'id': user.id
                });
            }

            // $("#container-time-now").html(dateFormat(new Date(), 1));
            // setInterval(() => {
            //     $("#container-time-now").html(dateFormat(new Date(), 1));
            // }, 5000);

            $('[data-bs-toggle="tooltip"]').attr('data-bs-placement', 'bottom')
            $('[data-bs-toggle="tooltip"]').tooltip()


            // Auto Logout & Session Checker
            if (@json(Auth::check())) {
                let idleTime = 0;
                const sessionLifetime = @json(config('session.lifetime')) * 60; // Konversi menit ke detik

                const resetIdleTimer = () => {
                    idleTime = 0;
                };

                $(document).on('mousemove keypress click scroll', resetIdleTimer);

                setInterval(() => {
                    idleTime += 5; // Cek setiap 5 detik
                    if (idleTime >= sessionLifetime) {
                        // Sebelum logout, cek status session ke server (jika user aktif di tab lain)
                        $.ajax({
                            url: "{{ route('check-session') }}",
                            method: 'GET',
                            dataType: 'json',
                            headers: {
                                'Accept': 'application/json'
                            }
                        }).done(function(response) {
                            if (response.authenticated) {
                                resetIdleTimer();
                            } else {
                                triggerLogout();
                            }
                        }).fail(function(xhr) {
                            triggerLogout();
                        });
                    }
                }, 5000);

                function triggerLogout() {
                    $(document).off('mousemove keypress click scroll', resetIdleTimer);
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sesi Berakhir',
                        text: 'Sesi Anda telah habis. Silakan login kembali.',
                        confirmButtonText: 'OK',
                        allowOutsideClick: false
                    }).then(() => {
                        document.getElementById('logout-form').submit();
                    });
                }
            }

            // Menangkap semua event saat modal manapun mulai ditutup
            document.addEventListener('hide.bs.modal', function(event) {
                if (document.activeElement) {
                    document.activeElement.blur(); // Hilangkan fokus dari tombol apapun
                }
            });

            // Inisialisasi semua tooltip di halaman
            // let tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            // tooltipTriggerList.map(function (tooltipTriggerEl) {
            //     return new bootstrap.Tooltip(tooltipTriggerEl)
            // })

            // Inisialisasi counter badge adendum khusus Staff Pengiriman
            if (role.includes('Staff Pengiriman')) {
                const loadAdendumBadge = () => {
                    ajaxGet('api/v1/adendum/list', {
                        limit: 1
                    }, result => {
                        let total = result.pagination?.total ?? 0;
                        if (total > 0) {
                            $('#adendum-sidebar-badge').text(total).removeClass('d-none');
                        } else {
                            $('#adendum-sidebar-badge').addClass('d-none');
                        }
                    });
                };
                loadAdendumBadge();
            }
        })
    </script>
</body>

</html>
