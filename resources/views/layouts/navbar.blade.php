<nav class="navbar navbar-expand-lg navbar-light">
    <ul class="navbar-nav w-100">
        <li class="nav-item d-block d-xl-none">
            <a class="nav-link sidebartoggler nav-icon-hover" id="headerCollapse" href="javascript:void(0)">
                <i class="bi bi-list"></i>
            </a>
        </li>
        <li class="navbar-collapse d-flex align-items-center">
            <h3 class="m-0 p-0">{{ $title }}</h3>
        </li>
    </ul>
    <div class="navbar-collapse justify-content-end px-0 w-100" id="navbarNav">
        <div class="d-flex align-items-center gap-3">
            <div id="container-time-now"></div>
            <div class="d-none d-md-block text-end lh-1">
                <div class="fw-bold text-dark mb-1">{{ Auth::user()->name ?? '-' }}</div>
                <div class="d-flex gap-1 justify-content-end">
                    @foreach(Auth::user()->getRoleNames() as $role)
                        <span class="badge bg-secondary py-1 px-2" style="font-size: 0.65rem;">{{ $role }}</span>
                    @endforeach
                </div>
            </div>

            <div id="container-notifikasi"></div>

            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle p-1 rounded-pill border border-2 border-white shadow-sm transition-all"
                    id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false"
                    style="outline: 2px solid #e9ecef;">
                        <div class="rounded-circle d-flex justify-content-center align-items-center text-white fw-bold shadow-sm"
                                style="width: 40px; height: 40px; background-color: #55c57a;">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                </a>

                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 mt-2 p-2"
                    aria-labelledby="userDropdown" style="min-width: 240px; animation: slideIn 0.2s ease;">
                    <li>
                        <div class="d-flex align-items-center p-2 mb-2 border-bottom pb-3">
                            <div class="rounded-circle d-flex justify-content-center align-items-center me-3 text-white fw-bold shadow-sm"
                                    style="width: 40px; height: 40px; background-color: #55c57a;">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <div class="overflow-hidden">
                                <h6 class="mb-0 fw-bold text-dark text-truncate">{{ Auth::user()->name }}</h6>
                                <small class="text-muted text-truncate d-block" style="font-size: 0.75rem;">
                                    {{ Auth::user()->email }}
                                </small>
                            </div>
                        </div>
                    </li>

                    <li>
                        <a class="dropdown-item d-flex align-items-center py-2 rounded-2" href="{{ url('userProfile') }}"> <i class="bi bi-person-gear me-3 text-primary fs-5"></i>
                            <div>
                                <span class="d-block fw-semibold text-dark small">My Profile</span>
                                <small class="text-muted" style="font-size: 0.65rem;">Edit akun & password</small>
                            </div>
                        </a>
                    </li>

                    @if(Auth::user()->hasRole('Pelanggan'))
                    <li>
                        <a class="dropdown-item d-flex align-items-center py-2 rounded-2" href="{{ url('userProfile') }}#instansi">
                            <i class="bi bi-building me-3 text-info fs-5"></i>
                            <div>
                                <span class="d-block fw-semibold text-dark small">Instansi</span>
                                {{-- <small class="text-muted" style="font-size: 0.65rem;"></small> --}}
                            </div>
                        </a>
                    </li>
                    @endif

                    {{-- <li>
                        <a class="dropdown-item d-flex align-items-center py-2 rounded-2" href="#">
                            <i class="bi bi-activity me-3 text-info fs-5"></i>
                            <div>
                                <span class="d-block fw-semibold text-dark small">Log Aktivitas</span>
                                <small class="text-muted" style="font-size: 0.65rem;">Riwayat login & aksi</small>
                            </div>
                        </a>
                    </li> --}}

                    <li><hr class="dropdown-divider my-2"></li>

                    <li>
                        <a class="dropdown-item d-flex align-items-center py-2 rounded-2 text-danger group-hover-red" href="{{ route('logout') }}"
                            onclick="event.preventDefault();
                                            document.getElementById('logout-form').submit();">
                            <i class="bi bi-box-arrow-right me-3 fs-5"></i> {{ __('Logout') }}
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </div>
        </div>
        {{-- <ul class="navbar-nav flex-row ms-0 align-items-center justify-content-end">
            <li class="nav-item text-end">
                {{ Auth::user()->name }}
                <br>
                @foreach(Auth::user()->getRoleNames() as $role)
                    <span class="badge text-bg-secondary">{{ $role }}</span>
                @endforeach
            </li>
            <li class="nav-item dropdown" id="container-notifikasi"></li>
            <li class="nav-item dropdown">
                <a class="nav-link nav-icon-hover" href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="{{ Auth::user()->profile && Auth::user()->profile->media ? asset('storage/images/avatar/'. Auth::user()->profile->media->file_hash) : asset('images/profile/user-1.jpg')}}" alt="" width="35" height="35" class="rounded-circle">
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up position-absolute" aria-labelledby="drop2">
                    <div class="message-body">
                        <a class="btn btn-outline-danger mx-3 mt-2 d-block" href="{{ route('logout') }}"
                            onclick="event.preventDefault();
                                            document.getElementById('logout-form').submit();">
                            {{ __('Logout') }}
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </div>
            </li>
        </ul> --}}
    </div>
</nav>
