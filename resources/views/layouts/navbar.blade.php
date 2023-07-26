<nav class="main-header navbar navbar-expand" style="background-color: #f1f1f1;">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link p-0 px-2" data-widget="pushmenu" href="#" role="button">
                <i class="bi bi-list" style="font-size: 1.8rem;"></i>
            </a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown ">
            <a id="navbarNotif" class="nav-link" data-bs-toggle="dropdown" role="button" href="#" data-bs-auto-close="outside">
                <i class="bi bi-bell-fill fs-4"></i>
                <span class="badge badge-danger" style="font-size: 9px; position: absolute; top: 5px; left: 5px;" id="count_lonceng">1</span>
            </a>
            <div class="dropdown-menu dropdown-menu-end " style="width: 350px">
                <div class="text-center text-muted">
                    <h5>Notifications</h5>
                </div>
                <div class="dropdown-divider my-0"></div>
                <div id="body-notif" class="p-2 overflow-auto bg-body-secondary" style="max-height: 400px;">
                    <div class="card shadow text-muted mb-1" role="button">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">Anda ditugaskan untuk layanan Uji Kebocoran Sumber Radioaktif pada tanggal 2023-07-26 08:00</div>
                                <div class="col-12 text-end">11:09, 26 july 2023</div>
                            </div>
                        </div>
                    </div>
                    <div class="card shadow text-muted mb-1" role="button">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">Anda ditugaskan untuk layanan Uji Kebocoran Sumber Radioaktif pada tanggal 2023-07-26 08:00</div>
                                <div class="col-12 text-end">11:09, 26 july 2023</div>
                            </div>
                        </div>
                    </div>
                    <div class="card shadow text-muted mb-1" role="button">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">Anda ditugaskan untuk layanan Uji Kebocoran Sumber Radioaktif pada tanggal 2023-07-26 08:00</div>
                                <div class="col-12 text-end">11:09, 26 july 2023</div>
                            </div>
                        </div>
                    </div>
                    <div class="card shadow text-muted mb-1" role="button">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">Anda ditugaskan untuk layanan Uji Kebocoran Sumber Radioaktif pada tanggal 2023-07-26 08:00</div>
                                <div class="col-12 text-end">11:09, 26 july 2023</div>
                            </div>
                        </div>
                    </div>
                    <div class="card shadow text-muted mb-1" role="button">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">Anda ditugaskan untuk layanan Uji Kebocoran Sumber Radioaktif pada tanggal 2023-07-26 08:00</div>
                                <div class="col-12 text-end">11:09, 26 july 2023</div>
                            </div>
                        </div>
                    </div>
                    <div class="card shadow text-muted mb-1" role="button">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">Anda ditugaskan untuk layanan Uji Kebocoran Sumber Radioaktif pada tanggal 2023-07-26 08:00</div>
                                <div class="col-12 text-end">11:09, 26 july 2023</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="dropdown-divider my-0"></div>
                <a href="javascript:void(0)" class="dropdown-item text-center text-muted" onclick="">View All</a>
            </div>
        </li>
        <li class="nav-item dropdown">
            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                {{ Auth::user()->name }} - {{ Auth::user()->getRoleNames()[0] }}
            </a>
            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <a class="dropdown-item" href="{{ route('logout') }}"
                    onclick="event.preventDefault();
                                    document.getElementById('logout-form').submit();">
                    {{ __('Logout') }}
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </li>
    <ul>
</nav>
