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
        </ul>
    </div>
</nav>
