<aside class="main-sidebar main-sidebar-custom sidebar-light-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('home') }}" class="brand-link link-offset-2 link-underline link-underline-opacity-0 text-dark"  style="background-color: #69c0ff;">
      <!-- <img src="../../dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8"> -->
      <span class="brand-text font-weight-light">Koperasi LAB</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="{{ route('home') }}" class="nav-link">
                      <i class="bi bi-person-badge-fill"></i>
                      <p>
                        Home
                      </p>
                    </a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('userProfile.index') }}" class="nav-link">
                    <i class="bi bi-person-fill"></i>
                    <p>
                      Profile
                    </p>
                  </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('userPerusahaan.index') }}" class="nav-link">
                      <i class="bi bi-building-fill"></i>
                      <p>
                        Profile Perusahaan
                      </p>
                    </a>
                </li>

                <li class="nav-header">USER MANAGEMENT</li>
                <li class="nav-item">
                    <a href="{{ route('users.index') }}" class="nav-link">
                      <i class="nav-icon bi bi-people-fill"></i>
                      <p>
                        Users
                      </p>
                    </a>
                </li>


            </ul>
        </nav>
    </div>
</aside>
