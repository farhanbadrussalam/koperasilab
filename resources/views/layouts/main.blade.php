<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="realtime" content="{{ Auth::user()->realtime_notifications ? '1' : '0' }}">
    <meta name="auth-id" content="{{ Auth::id() }}">

    <title>{{ config('app.name', 'Laravel') }} | {{ count(Auth::user()->getRoleNames()) != 0 ? Auth::user()->getRoleNames()[0] : '' }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=Nunito">
    <link rel="stylesheet" href="{{ asset('assets/font/allFont.css') }}">

    <!-- Theme adminLTE -->
    <link rel="stylesheet" href="{{ asset('assets/css/styles.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">

    {{-- Plugin --}}
    <link rel="stylesheet" href="{{ asset('assets/jquery/jquery-ui.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/DataTables/DataTables-1.13.5/css/dataTables.bootstrap5.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/sweetalert2/sweetalert2.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/dropify/css/dropify.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/select2/css/theme-bootstrap-5/select2-bootstrap-5-theme.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/flatpickr/flatpickr.min.css') }}">

    <!-- Scripts -->
    <script src="{{ asset('assets/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/jquery/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/inputmask/jquery.inputmask.min.js') }}"></script>
    <script src="{{ asset('assets/DataTables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('assets/DataTables/DataTables-1.13.5/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/DataTables/DataTables-1.13.5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/dropify/js/dropify.js') }}"></script>
    <script src="{{ asset('assets/js/app.min.js') }}"></script>
    {{-- Select 2 --}}
    <script src="{{ asset('vendor/select2/js/select2.full.js') }}"></script>
    {{-- Flat pickr --}}
    <script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('vendor/flatpickr/l10n/id.js') }}"></script>
    {{-- Signature --}}
    <script src="{{ asset('vendor/signature/signature_pad.umd.min.js') }}"></script>
    {{-- Magnific Popup --}}
    <link rel="stylesheet" href="{{ asset('vendor/magnific/magnific-popup.css') }}">
    <script src="{{ asset('vendor/magnific/jquery.magnific-popup.min.js') }}"></script>
    {{-- Parsley --}}
    <script src="{{ asset('vendor/parsley/parsley.min.js') }}"></script>
    <script src="{{ asset('vendor/parsley/setting.js') }}"></script>
    {{-- CKEditor --}}
    <script src="{{ asset('vendor/ckeditor/build/ckeditor.js') }}"></script>

    {{-- PeriodeJs --}}
    <script src="{{ asset('js/validation.js') }}"></script>
    <script src="{{ asset('js/SignatureSelect.js') }}"></script>
    <script src="{{ asset('js/periode.js') }}"></script>
    <script src="{{ asset('js/invoice.js') }}"></script>
    <script src="{{ asset('js/detail.js') }}"></script>
    <script src="{{ asset('js/upload.js') }}"></script>
    <script src="{{ asset('js/document.js') }}"></script>
    <script src="{{ asset('js/timeline.js') }}"></script>
    <script src="{{ asset('js/filter.js') }}"></script>
    <script src="{{ asset('js/cardList.js') }}"></script>
    <script src="{{ asset('js/inventory_tld.js') }}"></script>
    <script src="{{ asset('js/notifikasi.js') }}"></script>
    <script src="{{ asset('js/component/AdendumInformasi.js') }}"></script>

    {{-- Logo Aplikasi --}}
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body>

    <!--  Body Wrapper -->
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
        <!-- Main Sidebar Container -->
        @include('layouts.sidebar')

        <!--  Main wrapper -->
        <div class="body-wrapper">
            <header class="app-header shadow-sm">
                <!-- Navbar -->
                @include('layouts.navbar')

            </header>

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
        const role = @json(Auth::user()->getRoleNames());
        const permission = @json(Auth::user()->getDirectPermissions());
        const permissionInRole = @json(Auth::user()->getPermissionsViaRoles());
        const envirotment = "{{ config('app.env') }}";
        const statusUser = @json(Auth::user()->status);
    </script>
    <script src="{{ asset('assets/js/global.js') }}"></script>
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
            forceTLS: {{ config('broadcasting.connections.pusher.options.useTLS') ? "true" : "false" }},
        };
        $(function () {
            let user = @json(Auth::user());
            let notifikasi = new WidgetNotifikasi('container-notifikasi',{
                'channel' : `App.Models.User.${user.id}`,
                'id' : user.id
            });

            // $("#container-time-now").html(dateFormat(new Date(), 1));
            // setInterval(() => {
            //     $("#container-time-now").html(dateFormat(new Date(), 1));
            // }, 5000);

            $('[data-bs-toggle="tooltip"]').attr('data-bs-placement', 'bottom')
            $('[data-bs-toggle="tooltip"]').tooltip()


            // Mengecek session
            setInterval(() => {
                const authenticated = @json(Auth::check());
                if (!authenticated) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Session Expired',
                        text: 'Silahkan login kembali',
                        showConfirmButton: true
                    }).then(() => {
                        document.getElementById('logout-form').submit();
                    })
                };
                // ajaxGet(`check-session`, false, result => {
                //     if (!result.authenticated) {

                //     }
                // });
            }, 10000);

            // Menangkap semua event saat modal manapun mulai ditutup
            document.addEventListener('hide.bs.modal', function (event) {
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
                    ajaxGet('api/v1/pengiriman/listAdendum', { limit: 1 }, result => {
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

        function loadNotifikasi() {
            ajaxGet(`api/v1/getNotifikasi`, false, result => {
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
                case 'permohonan':
                    url = "{{ route('staff.permohonan') }}";
                    break;
                default:
                    break;
            }

            ajaxGet(`api/v1/getNotifikasi`, {id: notifId, status: 2}, result => {
                if(url){
                    window.location.href = url;
                }
            })
        }
    </script>
</body>

</html>
