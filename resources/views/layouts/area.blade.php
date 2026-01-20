<!DOCTYPE html>
<html lang="en" class="light-style customizer-hide" dir="ltr" data-theme="theme-default"
    data-assets-path="{{ asset('assets/') }}" data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Iseki Podium | Pokayoke Digital Unit Monitoring</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}"
        class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-auth.css') }}" />
    <link href="{{ asset('assets/css/datatables.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/fixedColumns.dataTables.min.css') }}" rel="stylesheet">
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
    @yield('style')
</head>

<body>
    <!-- Navbar -->
    <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
        id="layout-navbar">

        <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
            <!-- Search -->
            <div class="navbar-nav align-items-center">
                <div class="nav-item d-flex align-items-center">
                    <h5 class="text-primary mb-0">Digital Pokayoke</h5>
                </div>
            </div>

            <!-- Tombol-tombol di pojok kanan hanya muncul di layar XL dan besar -->
            <ul class="navbar-nav flex-row align-items-center ms-auto d-none d-xl-flex">
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <!-- Ganti dengan route kamu -->
                        <button class="btn btn-outline-secondary me-2">
                            Scan
                        </button>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <!-- Ganti dengan route kamu -->
                        <button class="btn btn-outline-secondary me-2">
                            Lineoff
                        </button>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <!-- Ganti dengan route kamu -->
                        <button class="btn btn-outline-secondary me-2">
                            Report
                        </button>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('logout.area') }}">
                        <!-- Ganti dengan route kamu -->
                        <button class="btn btn-outline-primary">
                            Logout
                        </button>
                    </a>
                </li>
            </ul>

            <!-- Dropdown menu di pojok kanan hanya muncul di layar kecil -->
            <ul class="navbar-nav flex-row align-items-center ms-auto d-xl-none">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                        <i class="bx bx-dots-vertical-rounded bx-sm"></i> <!-- Ikon titik tiga -->
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="bx bx-qr me-2"></i> Scan</a>
                        </li>
                        <li><a class="dropdown-item" href="#"><i
                                    class="bx bx-stop-circle me-2"></i> Lineoff</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bx bx-file me-2"></i>
                                Report</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="{{ route('logout.area') }}"><i class="bx bx-log-in me-2"></i>
                                Logout</a></li>
                    </ul>
                </li>
            </ul>

        </div>
    </nav>

    <div class="container-xxl flex-grow-1 container-p-y">
        @yield('content')
    </div>

    <!-- Core JS -->
    <!-- ... (script tags remain the same) ... -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="{{ asset('assets/js/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/dataTables.fixedColumns.min.js') }}"></script>
    @yield('script')
</body>

</html>
