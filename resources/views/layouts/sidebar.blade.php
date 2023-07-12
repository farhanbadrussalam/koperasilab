<aside class="main-sidebar main-sidebar-custom sidebar-light-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('home') }}" class="brand-link link-offset-2 link-underline link-underline-opacity-0 text-dark"  style="background-color: #f1f1f1;">
      <!-- <img src="../../dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8"> -->
      <span class="brand-text font-weight-light">Koperasi LAB</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="{{ route('layananJasa.index') }}" class="nav-link {{ Request::is('layananJasa*') ? 'active' : '' }}">
                      <i class="bi bi-briefcase-fill"></i>
                      <p>
                        Layanan Jasa
                      </p>
                    </a>
                </li>
                <li class="nav-header">USER MANAGEMENT</li>
                <li class="nav-item">
                    <a href="{{ route('users.index') }}" class="nav-link {{ Request::is('users*') ? 'active' : '' }}">
                      <i class="nav-icon bi bi-people-fill"></i>
                      <p>
                        Users
                      </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('roles.index') }}" class="nav-link {{ Request::is('roles*') ? 'active' : '' }}">
                      <i class="nav-icon bi bi-people-fill"></i>
                      <p>
                        Roles
                      </p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
