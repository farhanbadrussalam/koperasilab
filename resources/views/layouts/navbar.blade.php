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
        <ul class="navbar-nav flex-row ms-0 align-items-center justify-content-end">
            <li class="nav-item text-end">
                {{ Auth::user()->name }}
                <br>
                @foreach(Auth::user()->getRoleNames() as $role)
                    <span class="badge text-bg-secondary">{{ $role }}</span>
                @endforeach
            </li>
            <li class="nav-item dropdown">
                <a id="navbarNotif" class="nav-link position-relative" data-bs-toggle="dropdown" role="button" href="#" data-bs-auto-close="outside">
                    <i class="bi bi-bell-fill fs-4"></i>
                    <span class="position-absolute translate-middle badge rounded-pill bg-danger {{ !notifUnreadCount() ? 'd-none' : ''}}" style="font-size: 10px; top: 35%; right: 0px;" id="count_lonceng">{{notifUnreadCount()}}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up position-absolute" style="width: 350px">
                    <div class="text-center text-muted">
                        <h5>Notifications</h5>
                    </div>
                    <div class="dropdown-divider my-0"></div>
                    <div id="body-notif" class="p-2 overflow-auto bg-body-secondary" style="max-height: 400px;">
                        @forelse ($latestNotification as $item)
                            @switch($item->data['event'])
                                @case('Permohonan')
                                    <div class="card shadow text-muted mb-1 @if(!$item->read_at) bg-info-subtle @endif" data-id="${notif.id}" role="button" onclick="">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-12"><b>{{ explode('|', $item->data["perusahaan_id"])[1] }}</b> {{ $item->data["pesan"] }}</div>
                                                <small class="text-muted text-nowrap text-end">{{ $item->created_at->diffForHumans() }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    @break
                                @default
                                    <div class="card shadow text-muted mb-1 @if(!$item->read_at) bg-info-subtle @endif" data-id="${notif.id}" role="button">
                                        <a href="{{ url($item->data["url"]) }}" class="text-decoration-none text-muted">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-12">{!! $item->data["pesan"] !!}</div>
                                                <small class="text-muted text-nowrap text-end">{{ $item->created_at->diffForHumans() }}</small>
                                            </div>
                                        </div>
                                        </a>
                                    </div>

                            @endswitch
                        @empty
                            <span class="dropdown-item text-muted">Tidak ada notifikasi</span>
                        @endforelse
                    </div>
                    <div class="dropdown-divider my-0"></div>
                    <a href="javascript:void(0)" class="dropdown-item text-center text-muted" onclick="">View All</a>
                </div>
            </li>
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
        </ul>
    </div>
</nav>
