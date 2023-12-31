<aside class="main-sidebar main-sidebar-custom sidebar-light-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('home') }}" class="brand-link link-offset-2 link-underline link-underline-opacity-0 text-dark"
        style="background-color: #f1f1f1;">
        <!-- <img src="../../dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8"> -->
        <span class="brand-text font-weight-light fs-5">NuklindoLab Koperasi JKRL</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">
                @can('Home')
                    <li class="nav-item">
                        <a href="{{ route('home') }}" class="nav-link {{ Request::is('home*') ? 'active' : '' }}">
                            <i class="bi bi-person-badge-fill"></i>
                            <p>
                                Home
                            </p>
                        </a>
                    </li>
                @endcan

                @can('Biodata.pribadi')
                    <li class="nav-item">
                        <a href="{{ route('userProfile.index') }}"
                            class="nav-link {{ Request::is('userProfile*') ? 'active' : '' }}">
                            <i class="bi bi-person-fill"></i>
                            <p>
                                Profile
                            </p>
                        </a>
                    </li>
                @endcan

                @can('Biodata.perusahaan')
                    <li class="nav-item">
                        <a href="{{ route('userPerusahaan.index') }}"
                            class="nav-link {{ Request::is('userPerusahaan*') ? 'active' : '' }}">
                            <i class="bi bi-building-fill"></i>
                            <p>
                                Profile Perusahaan
                            </p>
                        </a>
                    </li>
                @endcan

                @can('Layananjasa')
                    <li class="nav-item">
                        <a href="{{ route('layananJasa.index') }}"
                            class="nav-link {{ Request::is('layananJasa*') ? 'active' : '' }}">
                            <i class="bi bi-briefcase-fill"></i>
                            <p>
                                Layanan Jasa
                            </p>
                        </a>
                    </li>
                @endcan

                @can('Penjadwalan')
                <li class="nav-item">
                    <a href="{{ route('jadwal.index') }}"
                        class="nav-link {{ Request::is('jadwal*') ? 'active' : '' }}">
                        <i class="bi bi-calendar2-event-fill"></i>
                        <p>
                            Jadwal Layanan
                        </p>
                    </a>
                </li>
                @endcan

                @can('Penugasan')
                <li class="nav-item">
                    <a href="{{ route('penugasan.index') }}"
                        class="nav-link {{ Request::is('penugasan*') ? 'active' : '' }}">
                        <i class="bi bi-calendar2-event-fill"></i>
                        <p>
                            Penugasan
                        </p>
                    </a>
                </li>
                @endcan

                @can('Permohonan')
                <li class="nav-item">
                    <a href="{{ route('permohonan.index') }}"
                        class="nav-link {{ Request::is('permohonan*') ? 'active' : '' }}">
                        <i class="bi bi-box-seam-fill"></i>
                        <p>
                            Permohonan Layanan
                        </p>
                    </a>
                </li>
                @endcan

                @can('Petugas')
                    <li class="nav-item">
                        <a href="{{ route('petugasLayanan.index') }}"
                            class="nav-link {{ Request::is('petugasLayanan*') ? 'active' : '' }}">
                            <i class="bi bi-postcard-fill"></i>
                            <p>
                                Petugas Layanan
                            </p>
                        </a>
                    </li>
                @endcan

                @can('User.management')
                    <li class="nav-header">USER MANAGEMENT</li>
                    <li class="nav-item">
                        <a href="{{ route('permission.index') }}" class="nav-link {{ Request::is('permission*') ? 'active' : '' }}">
                            <i class="bi bi-person-fill-slash fs-4"></i>
                            <p>
                                Permission
                            </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('roles.index') }}" class="nav-link {{ Request::is('roles*') ? 'active' : '' }}">
                            <i class="bi bi-person-fill-lock fs-4"></i>
                            <p>
                                Roles
                            </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('users.index') }}" class="nav-link {{ Request::is('users*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-people-fill"></i>
                            <p>
                                Users
                            </p>
                        </a>
                    </li>
                @endcan

                @can('Keuangan')
                    <li class="nav-item">
                        <a href="{{ route('keuangan.index') }}"
                            class="nav-link {{ Request::is('keuangan*') ? 'active' : '' }}">
                            <i class="bi bi-postcard-fill"></i>
                            <p>
                                Keuangan
                            </p>
                        </a>
                    </li>
                @endcan

                @can('lhukip')
                <li class="nav-item">
                    <a href="{{ route('manager.lhukip.index') }}"
                        class="nav-link {{ Request::is('manager*') ? 'active' : '' }}">
                        <i class="bi bi-box-seam-fill"></i>
                        <p>
                            LHU / KIP
                        </p>
                    </a>
                </li>
                @endcan

                @if(auth()->user()->can('Management.Lab') || auth()->user()->can('Management.Otorisasi'))
                <li class="nav-header">SERVICE USER MANAGEMENT</li>
                @can('Management.Lab')
                <li class="nav-item">
                    <a href="{{ route('lab.index') }}" class="nav-link {{ Request::is('lab*') ? 'active' : '' }}">
                        <i class="bi bi-person-workspace fs-4"></i>
                        <p>
                            Lab
                        </p>
                    </a>
                </li>
                @endcan
                @can('Management.Otorisasi')
                <li class="nav-item">
                    <a href="{{ route('otorisasi.index') }}" class="nav-link {{ Request::is('otorisasi*') ? 'active' : '' }}">
                        <i class="bi bi-person-gear fs-4"></i>
                        <p>
                            Otorisasi
                        </p>
                    </a>
                </li>
                @endcan
                @endif
            </ul>
        </nav>
    </div>
</aside>
