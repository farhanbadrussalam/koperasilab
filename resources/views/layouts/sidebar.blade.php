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
            <ul id="sidebarnav" class="p-0">
                <!-- MAIN MENU -->
                <li class="nav-small-cap">
                    <i class="bi bi-list nav-small-cap-icon fs-4"></i>
                    <span class="hide-menu">Main</span>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link {{ $module == 'home' ? 'active' : '' }}"
                        href="{{ route('home') }}" aria-expanded="false">
                        <span>
                            <i class="bi bi-person-badge-fill"></i>
                        </span>
                        <span class="hide-menu">Dashboard</span>
                    </a>
                </li>

                @can('Profile/pelanggan')
                <li class="sidebar-item">
                    <a class="sidebar-link {{ $module == 'profile-pelanggan' ? 'active' : '' }}"
                        href="{{ route('userProfile.index') }}" aria-expanded="false">
                        <span>
                            <i class="bi bi-person-fill"></i>
                        </span>
                        <span class="hide-menu">Profile</span>
                    </a>
                </li>
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
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ $module == 'permohonan-kontrak' ? 'active' : '' }}"
                            href="{{ route('permohonan.kontrak') }}" aria-expanded="false">
                            <span><i class="bi bi-card-list"></i></span>
                            <span class="hide-menu">Kontrak</span>
                        </a>
                    </li>
                @endcan

                @can('Tld')
                <li class="sidebar-item">
                    <a class="sidebar-link {{ $module == 'tld' ? 'active' : '' }}"
                        href="{{ route('tld.index') }}" aria-expanded="false">
                        <span><i class="bi bi-motherboard"></i></span>
                        <span class="hide-menu">Data TLD</span>
                    </a>
                </li>
                @endcan

                @can('pengguna')
                <li class="sidebar-item">
                    <a class="sidebar-link {{ $module == 'pengguna' ? 'active' : '' }}"
                        href="{{ route('userpengguna.index') }}" aria-expanded="false">
                        <span><i class="bi bi-people"></i></span>
                        <span class="hide-menu">Pengguna</span>
                    </a>
                </li>
                @endcan

                <!-- END MAIN MENU -->

                {{-- PERMOHONAN --}}
                @role('Pelanggan')
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
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link {{ $module == 'permohonan-pembayaran' ? 'active' : '' }}"
                        href="{{ route('permohonan.pembayaran') }}" aria-expanded="false">
                        <span><i class="bi bi-cash"></i></span>
                        <span class="hide-menu">Pembayaran</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link {{ $module == 'permohonan-pengiriman' ? 'active' : '' }}"
                        href="{{ route('permohonan.pengiriman') }}" aria-expanded="false">
                        <span><i class="bi bi-send"></i></span>
                        <span class="hide-menu">Pengiriman</span>
                    </a>
                </li>
                @endcan
                @endrole
                {{-- END PERMOHONAN --}}

                {{-- STAFF --}}
                @if(auth()->user()->hasAnyRole(['Staff Admin', 'Staff keuangan', 'Staff Penyelia', 'Staff LHU', 'Staff Pengiriman']))
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
                    </a>
                </li>
                @endcan

                @can('Staff/pengiriman')
                <li class="sidebar-item">
                    <a class="sidebar-link {{ $module == 'staff-pengiriman-permohonan' ? 'active' : '' }}"
                    href="{{ route('staff.pengiriman.permohonan') }}" aria-expanded="false">
                    <span><i class="bi bi-file-earmark-text"></i></span>
                    <span class="hide-menu">List Permohonan</span>
                    </a>
                </li>
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
                    </a>
                </li>
                @endcan

                @can('Staff/lhu')
                <li class="sidebar-item">
                    <a class="sidebar-link {{ $module == 'staff-lhu' ? 'active' : '' }}"
                    href="{{ route('staff.lhu') }}" aria-expanded="false">
                    <span><i class="bi bi-eyedropper"></i></span>
                    <span class="hide-menu">LHU</span>
                    </a>
                </li>
                @endcan

                @can('Staff/keuangan')
                <li class="sidebar-item">
                    <a class="sidebar-link {{ $module == 'staff-keuangan' ? 'active' : '' }}"
                    href="{{ route('staff.keuangan') }}" aria-expanded="false">
                    <i class="bi bi-wallet"></i>
                    <span class="hide-menu">Keuangan</span>
                    </a>
                </li>
                @endcan
                {{-- END STAFF --}}

                {{-- Manager --}}
                @if(auth()->user()->hasAnyRole(['Manager', 'General manager']))
                <li class="nav-small-cap">
                    <i class="bi bi-list nav-small-cap-icon fs-4"></i>
                    <span class="hide-menu">Manager</span>
                </li>
                @endif

                @can('Manager/pengajuan')
                <li class="sidebar-item">
                    <a class="sidebar-link {{ $module == 'manager-pengajuan' ? 'active' : '' }}"
                    href="{{ route('manager.pengajuan') }}" aria-expanded="false">
                    <i class="bi bi-file-earmark-ruled"></i>
                    <span class="hide-menu">Invoice</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link {{ $module == 'manager-suratTugas' ? 'active' : '' }}"
                    href="{{ route('manager.surat_tugas') }}" aria-expanded="false">
                    <i class="bi bi-journal-text"></i>
                    <span class="hide-menu">Surat Tugas</span>
                    </a>
                </li>
                @endcan

                @can('Management')
                <!-- MANAGEMENT MENU -->
                <li class="nav-small-cap cursoron" data-bs-toggle="collapse" data-bs-target="#collapseManagement" aria-expanded="false" aria-controls="collapseManagement">
                    <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="hide-menu">Management</span>
                        <i id="icon_collapse" class="bi {{ $title == 'Management' ? 'bi-chevron-up' : 'bi-chevron-down' }}"></i>
                    </div>
                </li>
                <div class="collapse {{ $title == 'Management' ? 'show' : '' }}" id="collapseManagement">
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
                        <a class="sidebar-link {{ $module == 'radiasi' ? 'active' : '' }}"
                            href="{{ route('radiasi.index') }}" aria-expanded="false">
                            <span>
                                <i class="bi bi-circle"></i>
                            </span>
                            <span class="hide-menu">Radiasi</span>
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
