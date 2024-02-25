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
        <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
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

                @can('Biodata.pribadi')
                <li class="sidebar-item">
                    <a class="sidebar-link {{ $module == 'biodata-pribadi' ? 'active' : '' }}"
                        href="{{ route('userProfile.index') }}" aria-expanded="false">
                        <span>
                            <i class="bi bi-person-fill"></i>
                        </span>
                        <span class="hide-menu">Profile</span>
                    </a>
                </li>
                @endcan

                @can('Layananjasa')
                <li class="sidebar-item">
                    <a class="sidebar-link {{ $module == 'Layananjasa' ? 'active' : '' }}"
                        href="{{ route('layananJasa.index') }}" aria-expanded="false">
                        <span>
                            <i class="bi bi-briefcase-fill"></i>
                        </span>
                        <span class="hide-menu">Layanan Jasa</span>
                    </a>
                </li>
                @endcan

                @can('Penjadwalan')
                <li class="sidebar-item">
                    <a class="sidebar-link {{ $module == 'jadwal' ? 'active' : '' }}"
                        href="{{ route('jadwal.index') }}" aria-expanded="false">
                        <span>
                            <i class="bi bi-calendar2-event-fill"></i>
                        </span>
                        <span class="hide-menu">Penjadwalan</span>
                    </a>
                </li>
                @endcan

                @can('Permohonan')
                <li class="sidebar-item">
                    <a class="sidebar-link {{ $module == 'Layananjasa' ? 'active' : '' }}"
                        href="{{ route('layananJasa.listLayanan') }}" aria-expanded="false">
                        <span>
                            <i class="bi bi- bi-briefcase-fill"></i>
                        </span>
                        <span class="hide-menu">Layanan jasa</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link {{ $module == 'permohonan' ? 'active' : '' }}"
                        href="{{ route('permohonan.index') }}" aria-expanded="false">
                        <span>
                            <i class="bi bi- bi-box-seam-fill"></i>
                        </span>
                        <span class="hide-menu">Permohonan Layanan</span>
                    </a>
                </li>
                @endcan

                @can('Petugas')
                <li class="sidebar-item">
                    <a class="sidebar-link {{ $module == 'petugas' ? 'active' : '' }}"
                        href="{{ route('petugasLayanan.index') }}" aria-expanded="false">
                        <span>
                            <i class="bi bi-postcard-fill"></i>
                        </span>
                        <span class="hide-menu">Petugas Layanan</span>
                    </a>
                </li>
                @endcan

                @can('Keuangan')
                <li class="sidebar-item">
                    <a class="sidebar-link {{ $module == 'keuangan' ? 'active' : '' }}"
                        href="{{ route('keuangan.index') }}" aria-expanded="false">
                        <span>
                            <i class="bi bi-postcard-fill"></i>
                        </span>
                        <span class="hide-menu">Keuangan</span>
                    </a>
                </li>
                @endcan

                @can('lhukip')
                <li class="sidebar-item">
                    <a class="sidebar-link {{ $module == 'lhukip' ? 'active' : '' }}"
                        href="{{ route('manager.lhukip.index') }}" aria-expanded="false">
                        <span>
                            <i class="bi bi-box-seam-fill"></i>
                        </span>
                        <span class="hide-menu">LHU / KIP</span>
                    </a>
                </li>
                @endcan

                @if(auth()->user()->hasPermissionTo('Otorisasi-Front desk'))
                <li class="sidebar-item">
                    <a class="sidebar-link {{ $module == 'frontdesk' ? 'active' : '' }}"
                        href="{{ route('jobs.frontdesk.index') }}" aria-expanded="false">
                        <span>
                            <i class="bi bi-box-seam-fill"></i>
                        </span>
                        <span class="hide-menu">Front desk</span>
                    </a>
                </li>
                @endif

                @if(auth()->user()->hasPermissionTo('Otorisasi-Pelaksana kontrak'))
                <li class="sidebar-item">
                    <a class="sidebar-link {{ $module == 'pelaksanakontrak' ? 'active' : '' }}"
                        href="{{ route('jobs.pelaksana.index') }}" aria-expanded="false">
                        <span>
                            <i class="bi bi-box-seam-fill"></i>
                        </span>
                        <span class="hide-menu">Pelaksana kontrak</span>
                    </a>
                </li>
                @endif

                @if(auth()->user()->hasPermissionTo('Otorisasi-Penyelia LAB'))
                <li class="sidebar-item">
                    <a class="sidebar-link {{ $module == 'penyelialab' ? 'active' : '' }}"
                        href="{{ route('jobs.penyelia.index') }}" aria-expanded="false">
                        <span>
                            <i class="bi bi-box-seam-fill"></i>
                        </span>
                        <span class="hide-menu">Penyelia LAB</span>
                    </a>
                </li>
                @endif

                @if(auth()->user()->hasPermissionTo('Otorisasi-Pelaksana LAB'))
                <li class="sidebar-item">
                    <a class="sidebar-link {{ $module == 'pelaksanalab' ? 'active' : '' }}"
                        href="{{ route('jobs.pelaksanaLab.index') }}" aria-expanded="false">
                        <span>
                            <i class="bi bi-box-seam-fill"></i>
                        </span>
                        <span class="hide-menu">Pelaksana LAB</span>
                    </a>
                </li>
                @endif
                <!-- END MAIN MENU -->

                @can('User.management')
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
                        <a class="sidebar-link {{ $module == 'lab' ? 'active' : '' }}"
                            href="{{ route('lab.index') }}" aria-expanded="false">
                            <span>
                                <i class="bi bi-circle"></i>
                            </span>
                            <span class="hide-menu">Lab</span>
                        </a>
                    </li>
                </div>
                <!-- END MANAGEMENT MENU -->
                @endcan
            </ul>
        </nav>
    </div>
</aside>
