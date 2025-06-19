<div class="navbar-custom">
    <div class="topbar container-fluid">
        <div class="d-flex align-items-center gap-lg-2 gap-1">


            <div class="logo-topbar">

                <a href="<?= url('dashboard') ?>" class="logo-light">
                    <span class="logo-lg">
                        <img src="{{ asset('assets') }}/images/logo.png" alt="logo">
                    </span>
                    <span class="logo-sm">
                        <img src="{{ asset('assets') }}/images/logo-sm.png" alt="small logo">
                    </span>
                </a>


                <a href="<?= url('dashboard') ?>" class="logo-dark">
                    <span class="logo-lg">
                        <img src="{{ asset('assets') }}/images/logo-dark.png" alt="dark logo">
                    </span>
                    <span class="logo-sm">
                        <img src="{{ asset('assets') }}/images/logo-sm.png" alt="small logo">
                    </span>
                </a>
            </div>


            <button class="button-toggle-menu">
                <i class="ri-menu-2-fill"></i>
            </button>


            <button class="navbar-toggle" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                <div class="lines">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </button>

        </div>

        <ul class="topbar-menu d-flex align-items-center gap-3">


            <li class="d-none d-sm-inline-block">
                <div class="nav-link" id="light-dark-mode" data-bs-toggle="tooltip" data-bs-placement="left" title="Theme Mode">
                    <i class="ri-moon-line fs-22"></i>
                </div>
            </li>


            <li class="d-none d-md-inline-block">
                <a class="nav-link" href="" data-toggle="fullscreen">
                    <i class="ri-fullscreen-line fs-22"></i>
                </a>
            </li>

            <li class="dropdown">
                <a class="nav-link dropdown-toggle arrow-none nav-user px-2" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                    <span class="account-user-avatar">
                        <img src="<?= session('user')[0]['foto'] !== '' ? session('user')[0]['foto'] : asset('assets') . "/images/users/avatar-1.jpg" ?>" alt="user-image" width="32" class="rounded-circle">
                    </span>
                    <span class="d-lg-flex flex-column gap-1 d-none">
                        <h5 class="my-0"><?= session('user')[0]['nama'] ?></h5>
                        <h6 class="my-0 fw-normal"><?= session('user')[0]['id_role'] == '1' ? 'Admin' : (session('user')[0]['id_role'] == '2' ? 'Dosen' : 'Mahasiswa') ?></h6>
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated profile-dropdown">

                    <div class=" dropdown-header noti-title">
                        <h6 class="text-overflow m-0">Welcome !</h6>
                    </div>

                    <a href="{{ url('/profile') }}" class="dropdown-item">
                        <i class="ri-account-circle-line fs-18 align-middle me-1"></i>
                        <span>Profil</span>
                    </a>

                    <a href="{{ url('/auth/logout') }}" class="dropdown-item">
                        <i class="ri-logout-box-line fs-18 align-middle me-1"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </li>
        </ul>
    </div>
</div>