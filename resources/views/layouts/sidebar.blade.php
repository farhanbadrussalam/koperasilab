@php
    $hiddenPelanggan = false;
    $rolePelanggan = Auth::user()->hasRole('Pelanggan');
    if (
        $rolePelanggan &&
        ((isset(Auth::user()->profile) && Auth::user()->profile->nik == null) || Auth::user()->id_perusahaan == null)
    ) {
        $hiddenPelanggan = true;
    }
@endphp
<aside class="left-sidebar">
    <!-- Sidebar scroll-->
    <div>
        <div class="brand-logo d-flex align-items-center justify-content-between">
            <a href="<?= url('/') ?>" class="text-nowrap logo-img">
                <span class="brand-text font-weight-light fs-5">Koperasi JKRL</span>
            </a>
            <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
                <i class="bi bi-x fs-8"></i>
            </div>
        </div>

        <!-- Sidebar navigation-->
        <nav class="sidebar-nav scroll-sidebar shadow-sm" data-simplebar="">
            <!-- Sidebar User Profile Card -->
            <div class="sidebar-profile-card">
                <div class="profile-name" title="{{ Auth::user()->name }}">{{ Auth::user()->name }}</div>
                <div class="profile-role">
                    {{ count(Auth::user()->getRoleNames()) != 0 ? Auth::user()->getRoleNames()[0] : 'Member' }}
                </div>
                @if (count(Auth::user()->satuankerja) > 0)
                    <div class="profile-satuan-container">
                        @foreach (Auth::user()->satuankerja as $satuan)
                            <span class="badge-satuan" title="{{ $satuan->name }}">{{ $satuan->name }}</span>
                        @endforeach
                    </div>
                @endif
            </div>

            <ul id="sidebarnav" class="p-0">
                <!-- MAIN MENU -->
                <li class="nav-small-cap">
                    <i class="bi bi-list nav-small-cap-icon fs-4"></i>
                    <span class="hide-menu">Main</span>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link {{ $module == 'home' ? 'active' : '' }}" href="{{ route('home') }}"
                        aria-expanded="false">
                        <span>
                            <i class="bi bi-person-badge-fill"></i>
                        </span>
                        <span class="hide-menu">Dashboard</span>
                    </a>
                </li>

                @can('Profile/pelanggan')
                    {{-- <li class="sidebar-item">
                    <a class="sidebar-link {{ $module == 'profile-pelanggan' ? 'active' : '' }}"
                href="{{ route('userProfile.index') }}" aria-expanded="false">
                <span>
                    <i class="bi bi-person-fill"></i>
                </span>
                <span class="hide-menu">Profile</span>
                </a>
                </li> --}}
                @endcan

                @can('Staff/perusahaan')
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ $module == 'staff-perusahaan' ? 'active' : '' }}"
                            href="{{ route('staff.perusahaan') }}" aria-expanded="false">
                            <span>
                                <i class="bi bi-building"></i>
                            </span>
                            <span class="hide-menu">Perusahaan</span>
                        </a>
                    </li>
                @endcan

                @can('Kontrak')
                    <li class="sidebar-item @if ($hiddenPelanggan) d-none @endif">
                        <a class="sidebar-link {{ $module == 'permohonan-kontrak' ? 'active' : '' }}"
                            href="{{ route('permohonan.kontrak') }}" aria-expanded="false">
                            <span><i class="bi bi-card-list"></i></span>
                            <span class="hide-menu">Kontrak</span>
                        </a>
                    </li>
                @endcan

                @can('Tld')
                    @if ((Auth::user()->id_perusahaan && Auth::user()->status == 1) || !Auth::user()->hasRole('Pelanggan'))
                        <li class="sidebar-item @if ($hiddenPelanggan) d-none @endif">
                            <a class="sidebar-link {{ $module == 'tld' ? 'active' : '' }}" href="{{ route('tld.index') }}"
                                aria-expanded="false">
                                <span><i class="bi bi-motherboard"></i></span>
                                <span class="hide-menu">Data TLD</span>
                            </a>
                        </li>
                    @endif
                @endcan

                @can('pengguna')
                    @if (Auth::user()->id_perusahaan && Auth::user()->status == 1)
                        <li class="sidebar-item @if ($hiddenPelanggan) d-none @endif">
                            <a class="sidebar-link {{ $module == 'pengguna' ? 'active' : '' }}"
                                href="{{ route('userpengguna.index') }}" aria-expanded="false">
                                <span><i class="bi bi-people"></i></span>
                                <span class="hide-menu">Pengguna</span>
                            </a>
                        </li>
                    @endif
                @endcan

                <!-- END MAIN MENU -->

                {{-- PERMOHONAN --}}
                @role('Pelanggan')
                    @if (!$hiddenPelanggan && Auth::user()->status == 1)
                        <li class="nav-small-cap">
                            <i class="bi bi-list nav-small-cap-icon fs-4"></i>
                            <span class="hide-menu">Permohonan</span>
                        </li>

                        @can('Permohonan/pengajuan')
                            <li class="sidebar-item">
                                <a class="sidebar-link {{ $module == 'permohonan-pengajuan' ? 'active' : '' }}"
                                    href="{{ route('permohonan.pengajuan') }}" aria-expanded="false">
                                    <span><i class="bi bi-file-earmark-text"></i></span>
                                    <span class="hide-menu">Pengajuan</span>
                                </a>
                            </li>
                            <li class="sidebar-item">
                                <a class="sidebar-link {{ $module == 'permohonan-dikembalikan' ? 'active' : '' }}"
                                    href="{{ route('permohonan.dikembalikan') }}" aria-expanded="false">
                                    <span><i class="bi bi-arrow-counterclockwise"></i></span>
                                    <span class="hide-menu">Dikembalikan</span>
                                    <span
                                        class="badge rounded-pill ms-auto bg-danger @if (!notifUnreadCount('Dikembalikan')) d-none @endif">{{ notifUnreadCount('Dikembalikan') }}</span>
                                </a>
                            </li>
                            <li class="sidebar-item">
                                <a class="sidebar-link {{ $module == 'permohonan-pembayaran' ? 'active' : '' }}"
                                    href="{{ route('permohonan.pembayaran') }}" aria-expanded="false">
                                    <span><i class="bi bi-cash"></i></span>
                                    <span class="hide-menu">Pembayaran</span>
                                    <span
                                        class="badge rounded-pill ms-auto bg-danger @if (!notifUnreadCount('Keuangan')) d-none @endif">{{ notifUnreadCount('Keuangan') }}</span>
                                </a>
                            </li>
                            <li class="sidebar-item">
                                <a class="sidebar-link {{ $module == 'permohonan-pengiriman' ? 'active' : '' }}"
                                    href="{{ route('permohonan.pengiriman') }}" aria-expanded="false">
                                    <span><i class="bi bi-send"></i></span>
                                    <span class="hide-menu">Pengiriman</span>
                                    <span
                                        class="badge rounded-pill ms-auto bg-danger @if (!notifUnreadCount('pengiriman')) d-none @endif">{{ notifUnreadCount('pengiriman') }}</span>
                                </a>
                            </li>
                        @endcan
                    @endif
                @endrole
                {{-- END PERMOHONAN --}}

                {{-- STAFF --}}
                @if (Auth::user()->hasAnyRole(['Staff Admin', 'Staff keuangan', 'Staff Penyelia', 'Staff LHU']))
                    <li class="nav-small-cap">
                        <i class="bi bi-list nav-small-cap-icon fs-4"></i>
                        <span class="hide-menu">STAFF</span>
                    </li>
                @endif

                @can('Staff/lhu/petugas')
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ $module == 'staff-petugas-lhu' ? 'active' : '' }}"
                            href="{{ route('staff.lhu.petugas') }}" aria-expanded="false">
                            <span>
                                <i class="bi bi-person-fill"></i>
                            </span>
                            <span class="hide-menu">Data Petugas</span>
                        </a>
                    </li>
                @endcan

                @can('Staff/permohonan')
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ $module == 'staff-permohonan' ? 'active' : '' }}"
                            href="{{ route('staff.permohonan') }}" aria-expanded="false">
                            <span><i class="bi bi-file-earmark-text"></i></span>
                            <span class="hide-menu">Permohonan</span>
                            <span
                                class="badge rounded-pill ms-auto bg-danger @if (!notifUnreadCount('Permohonan')) d-none @endif">{{ notifUnreadCount('Permohonan') }}</span>
                        </a>
                    </li>
                @endcan

                @can('Request_users')
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ $module == 'staff-approval' ? 'active' : '' }}"
                            href="{{ route('staff.pelanggan.approval') }}" aria-expanded="false">
                            <span><i class="bi bi-file-earmark-text"></i></span>
                            <span class="hide-menu">Approval Pelanggan</span>
                        </a>
                    </li>
                @endcan

                @can('Staff/pengiriman')
                    <li class="nav-small-cap">
                        <i class="bi bi-list nav-small-cap-icon fs-4"></i>
                        <span class="hide-menu">PENGIRIMAN</span>
                    </li>
                    {{-- <li class="sidebar-item">
                    <a class="sidebar-link {{ $module == 'staff-pengiriman-permohonan' ? 'active' : '' }}"
                        href="{{ route('staff.pengiriman.permohonan') }}" aria-expanded="false">
                        <span><i class="bi bi-file-earmark-text"></i></span>
                        <span class="hide-menu">Daftar Permohonan</span>
                        <span class="badge rounded-pill ms-auto bg-danger @if (!notifUnreadCount(['PenyeliaLAB'])) d-none @endif">{{ notifUnreadCount(['PenyeliaLAB']) }}</span>
                    </a>
                </li> --}}
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ $module == 'staff-pengiriman' ? 'active' : '' }}"
                            href="{{ route('staff.pengiriman') }}" aria-expanded="false">
                            <span><i class="bi bi-send"></i></span>
                            <span class="hide-menu">List Pengiriman</span>
                        </a>
                    </li>
                @endcan

                @can('Staff/penyelia')
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ $module == 'staff-penyelia' ? 'active' : '' }}"
                            href="{{ route('staff.penyelia') }}" aria-expanded="false">
                            <span><i class="bi bi-eyedropper"></i></span>
                            <span class="hide-menu">Penyeliaan</span>
                            <span
                                class="badge rounded-pill ms-auto bg-danger @if (!notifUnreadCount(['Penyelia', 'PenyeliaLAB'])) d-none @endif">{{ notifUnreadCount(['Penyelia', 'PenyeliaLAB']) }}</span>
                        </a>
                    </li>
                @endcan

                @can('Staff/lhu')
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ $module == 'staff-lhu' ? 'active' : '' }}"
                            href="{{ route('staff.lhu') }}" aria-expanded="false">
                            <span><i class="bi bi-eyedropper"></i></span>
                            <span class="hide-menu">LHU</span>
                            <span
                                class="badge rounded-pill ms-auto bg-danger @if (!notifUnreadCount('PenyeliaLAB')) d-none @endif">{{ notifUnreadCount('PenyeliaLAB') }}</span>
                        </a>
                    </li>
                @endcan

                @can('Staff/keuangan')
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ $module == 'staff-keuangan' ? 'active' : '' }}"
                            href="{{ route('staff.keuangan') }}" aria-expanded="false">
                            <i class="bi bi-wallet"></i>
                            <span class="hide-menu">Keuangan</span>
                            <span
                                class="badge rounded-pill ms-auto bg-danger @if (!notifUnreadCount('Keuangan')) d-none @endif">{{ notifUnreadCount('Keuangan') }}</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ $module == 'staff-jenis-pembayaran' ? 'active' : '' }}"
                            href="{{ route('staff.jenis.pembayaran') }}" aria-expanded="false">
                            <i class="bi bi-credit-card"></i>
                            <span class="hide-menu">Metode Pembayaran</span>
                        </a>
                    </li>
                @endcan
                {{-- END STAFF --}}

                {{-- Manager --}}
                @if (Auth::user()->hasAnyRole(['Manager', 'General manager']))
                    <li class="nav-small-cap">
                        <i class="bi bi-list nav-small-cap-icon fs-4"></i>
                        <span class="hide-menu">Manager</span>
                    </li>
                @endif

                @can('Manager/keuangan')
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ $module == 'manager-pengajuan' ? 'active' : '' }}"
                            href="{{ route('manager.pengajuan') }}" aria-expanded="false">
                            <i class="bi bi-file-earmark-ruled"></i>
                            <span class="hide-menu">Verifikasi Keuangan</span>
                            <span
                                class="badge rounded-pill ms-auto bg-danger @if (!notifUnreadCount('Keuangan')) d-none @endif">{{ notifUnreadCount('Keuangan') }}</span>
                        </a>
                    </li>
                @endcan

                @can('Manager/pengajuan')
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ $module == 'manager-suratTugas' ? 'active' : '' }}"
                            href="{{ route('manager.surat_tugas') }}" aria-expanded="false">
                            <i class="bi bi-journal-text"></i>
                            <span class="hide-menu">Persetujuan Operasional</span>
                            <span
                                class="badge rounded-pill ms-auto bg-danger @if (!notifUnreadCount('SuratTugas')) d-none @endif">{{ notifUnreadCount('SuratTugas') }}</span>
                        </a>
                    </li>
                @endcan
                @can('Manager/surpeng')
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ $module == 'manager-surpeng' ? 'active' : '' }}"
                            href="{{ route('manager.surpeng') }}" aria-expanded="false">
                            <i class="bi bi-envelope-paper"></i>
                            <span class="hide-menu">Surat Pengantar</span>
                            <span
                                class="badge rounded-pill ms-auto bg-danger @if (!notifUnreadCount('Surpeng')) d-none @endif">{{ notifUnreadCount('Surpeng') }}</span>
                        </a>
                    </li>
                @endcan

                @can('Manager/produktivitas')
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ $module == 'manager-produktivitas' ? 'active' : '' }}"
                            href="{{ route('manager.produktivitas') }}" aria-expanded="false">
                            <i class="bi bi-bar-chart-line-fill"></i>
                            <span class="hide-menu">Produktivitas Petugas</span>
                        </a>
                    </li>
                @endcan

                @can('Management')
                    <!-- MANAGEMENT MENU -->
                    <li class="nav-small-cap cursoron" data-bs-toggle="collapse" data-bs-target="#collapseManagement"
                        aria-expanded="{{ $title == 'Management' ? 'true' : 'false' }}"
                        aria-controls="collapseManagement">
                        <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <span class="hide-menu">Management</span>
                            <i id="icon_collapse" class="bi bi-chevron-down"></i>
                        </div>
                    </li>
                    <div class="collapse sidebar-submenu {{ $title == 'Management' ? 'show' : '' }}"
                        id="collapseManagement">
                        <li class="sidebar-item">
                            <a class="sidebar-link {{ $module == 'permission' ? 'active' : '' }}"
                                href="{{ route('permission.index') }}" aria-expanded="false">
                                <span>
                                    <i class="bi bi-circle"></i>
                                </span>
                                <span class="hide-menu">Permission</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link {{ $module == 'roles' ? 'active' : '' }}"
                                href="{{ route('roles.index') }}" aria-expanded="false">
                                <span>
                                    <i class="bi bi-circle"></i>
                                </span>
                                <span class="hide-menu">Roles</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link {{ $module == 'users' ? 'active' : '' }}"
                                href="{{ route('users.index') }}" aria-expanded="false">
                                <span>
                                    <i class="bi bi-circle"></i>
                                </span>
                                <span class="hide-menu">Users</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link {{ $module == 'tld' ? 'active' : '' }}"
                                href="{{ route('tld.index') }}" aria-expanded="false">
                                <span>
                                    <i class="bi bi-circle"></i>
                                </span>
                                <span class="hide-menu">TLD</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link {{ $module == 'radiasi' ? 'active' : '' }}"
                                href="{{ route('radiasi.index') }}" aria-expanded="false">
                                <span>
                                    <i class="bi bi-circle"></i>
                                </span>
                                <span class="hide-menu">Radiasi</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link {{ $module == 'document' ? 'active' : '' }}"
                                href="{{ route('document.index') }}" aria-expanded="false">
                                <span>
                                    <i class="bi bi-circle"></i>
                                </span>
                                <span class="hide-menu">Document</span>
                            </a>
                        </li>
                        {{-- <li class="sidebar-item">
                        <a class="sidebar-link {{ $module == 'lab' ? 'active' : '' }}"
                    href="{{ route('lab.index') }}" aria-expanded="false">
                    <span>
                        <i class="bi bi-circle"></i>
                    </span>
                    <span class="hide-menu">Lab</span>
                    </a>
                    </li> --}}
                    </div>
                    <!-- END MANAGEMENT MENU -->
                @endcan
            </ul>
        </nav>
    </div>
</aside>
