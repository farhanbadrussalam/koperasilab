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
                <span class="badge badge-danger" style="font-size: 9px; position: absolute; top: 5px; left: 5px;display: none;" id="count_lonceng">1</span>
            </a>
            <div class="dropdown-menu dropdown-menu-end " style="width: 350px">
                <div class="text-center text-muted">
                    <h5>Notifications</h5>
                </div>
                <div class="dropdown-divider my-0"></div>
                <div id="body-notif" class="p-2 overflow-auto bg-body-secondary" style="max-height: 400px;">

                </div>
                <div class="dropdown-divider my-0"></div>
                <a href="javascript:void(0)" class="dropdown-item text-center text-muted" onclick="">View All</a>
            </div>
        </li>
        <li class="nav-item dropdown">
            <?php
                $statusVerif = Auth::user()->petugasLayanan ? Auth::user()->petugasLayanan->status_verif : null;
                $otorisasi = Auth::user()->getDirectPermissions();
            ?>
            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                {{ Auth::user()->name }} - {{ count($otorisasi) != 0 ? "Petugas Layanan" : Auth::user()->getRoleNames()[0] }}
            </a>
            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                @if (count($otorisasi) != 0)
                <div class="dropdown-item">
                    <span>Otorisasi <span class="@if($statusVerif==1) text-danger @else text-primary @endif">(@if($statusVerif==1) Not verif @else Verifikasi @endif)</span></span>
                    <ul>
                        @foreach ($otorisasi as $val)
                            <li>{{stringSplit($val->name, 'Otorisasi-')}}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
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
