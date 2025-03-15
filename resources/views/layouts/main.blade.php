<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} | {{ Auth::user()->getRoleNames()[0] }}</title>

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
    <link rel="stylesheet" href="{{ asset('assets/toast/toastr.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/DataTables/DataTables-1.13.5/css/dataTables.bootstrap5.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/sweetalert2/sweetalert2.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/dropify/css/dropify.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/select2/css/theme-bootstrap-5/select2-bootstrap-5-theme.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/flatpickr/flatpickr.min.css') }}">

    <!-- Scripts -->
    {{-- <link rel="stylesheet" href="{{ asset('assets/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bootstrap-icons/font/bootstrap-icons.min.css') }}">
    <script src="{{ asset('assets/bootstrap/dist/js/bootstrap.min.js') }}"></script> --}}
    
    <script src="{{ asset('assets/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/jquery/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/inputmask/jquery.inputmask.min.js') }}"></script>
    <script src="{{ asset('assets/DataTables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/toast/toastr.min.js') }}"></script>
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

    {{-- PeriodeJs --}}
    <script src="{{ asset('js/periode.js') }}"></script>
    <script src="{{ asset('js/invoice.js') }}"></script>
    <script src="{{ asset('js/detail.js') }}"></script>
    <script src="{{ asset('js/upload.js') }}"></script>
    <script src="{{ asset('js/document.js') }}"></script>
    <script src="{{ asset('js/timeline.js') }}"></script>
    <script src="{{ asset('js/filter.js') }}"></script>
    <script src="{{ asset('js/cardList.js') }}"></script>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body>
    <input type="hidden" name="bearer" id="bearer-token" value="{{ generateToken() }}">
    <input type="hidden" name="csrf" id="csrf-token" value="{{ csrf_token() }}">
    <input type="hidden" id="base_url" value="{{ url('') }}">
    <input type="hidden" id="userActive" value="{{ Auth::user() }}">
    <input type="hidden" id="role" value="{{ Auth::user()->getRoleNames()[0] }}">
    <input type="hidden" id="permission" value="{{ Auth::user()->getDirectPermissions() }}">
    <input type="hidden" id="permissionInRole" value="{{ Auth::user()->getPermissionsViaRoles() }}">

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
    <div class="modal fade" id="modal-preview-ktp" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="">KTP</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body row justify-content-center">
                    <img src="#" alt="" class="img-fluid" id="img-preview-ktp">
                </div>
            </div>
        </div>
    </div>

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
        @endif

        $(function () {
            let user = @json(Auth::user());

            loadNotifikasi();
            let chanel = window.Echo?.private(`jadwal.${user.id}`).listen('.notif', (result) => {
                toastr.info(
                    `
                        <div>${result.data.type.toUpperCase()}</div>
                        <div>${result.message}</div>
                    `
                );
                loadNotifikasi();
            })

            $('[data-bs-toggle="tooltip"]').attr('data-bs-placement', 'bottom')
            $('[data-bs-toggle="tooltip"]').tooltip()

            $("#collapseManagement").on('show.bs.collapse', function () {
                $('#icon_collapse').addClass('bi-chevron-up');
                $('#icon_collapse').removeClass('bi-chevron-down');
            });

            $("#collapseManagement").on('hide.bs.collapse', function () {
                $('#icon_collapse').addClass('bi-chevron-down');
                $('#icon_collapse').removeClass('bi-chevron-up');
            });
        })

        function loadNotifikasi() {
            $.ajax({
                url: "{{ url('api/v1/getNotifikasi') }}",
                dataType: 'json',
                method: 'GET',
                processData: true,
                headers: {
                    'Authorization' : `Bearer {{ generateToken() }}`,
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
                case 'permohonan':
                    url = "{{ route('staff.permohonan') }}";
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
                    'Authorization' : `Bearer {{ generateToken() }}`,
                    'Content-Type': 'application/json'
                },
            }).done(result => {
                // console.log(result);
                if(url){
                    window.location.href = url;
                }
            })
        }
    </script>
</body>

</html>
