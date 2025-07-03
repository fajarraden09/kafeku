<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" href="{{ asset('gambar/logo1.png') }}" type="image/png">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="{{ asset('Lte/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="{{ asset('Lte/dist/css/adminlte.min.css') }}">
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">

        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>
            </ul>
        </nav>
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="{{ route('owner.dashboard') }}" class="brand-link">
                <img src="{{ asset('gambar/logo1.png') }}" alt="AdminLTE Logo"
                    class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light">Sugriwa Subali</span>
            </a>

            <div class="sidebar">
                @auth
                    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                        <div class="info">
                            <a href="#" class="d-block">{{ auth()->user()->name }}</a>
                        </div>
                    </div>
                @endauth

                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        {{-- Kumpulan Menu --}}
                        <li class="nav-item">
                            <a href="{{ route('owner.dashboard') }}"
                                class="nav-link {{ request()->is('owner/dashboard') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('owner.kasir.index') }}"
                                class="nav-link {{ request()->is('owner/kasir*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-cash-register"></i>
                                <p>
                                    Menu Kasir
                                    @if (isset($unpaidOrdersCount) && $unpaidOrdersCount > 0)
                                        <span class="right badge badge-warning">{{ $unpaidOrdersCount }}</span>
                                    @endif
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('owner.laporan.index') }}"
                                class="nav-link {{ request()->is('owner/laporan') && !request()->is('owner/laporan/stok') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-file-alt"></i>
                                <p>Riwayat Kasir</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('owner.menu') }}"
                                class="nav-link {{ request()->is('owner/menu*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-utensils"></i>
                                <p>Data Menu</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('owner.bahan_baku') }}"
                                class="nav-link {{ request()->is('owner/bahan_baku*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-box"></i>
                                <p>Data Stok Bahan Baku</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('owner.bahan_masuk') }}"
                                class="nav-link {{ request()->is('owner/bahan_masuk*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-arrow-circle-down"></i>
                                <p>Bahan Baku Masuk</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('owner.bahan_keluar') }}"
                                class="nav-link {{ request()->is('owner/bahan_keluar*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-arrow-circle-up"></i>
                                <p>Bahan Baku Keluar</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('owner.laporan.stok') }}"
                                class="nav-link {{ request()->is('owner/laporan/stok*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-exchange-alt"></i>
                                <p>Laporan Stok</p>
                            </a>
                        </li>
                        @if(auth()->user()->role == 'owner')
                            <li class="nav-item">
                                <a href="{{ route('owner.akun') }}"
                                    class="nav-link {{ request()->is('owner/Akun_pengguna*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-users-cog"></i>
                                    <p>Akun Pengguna</p>
                                </a>
                            </li>
                        @endif
                        <li class="nav-item">
                            <a href="{{ route('logout') }}" class="nav-link">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <p>Logout</p>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>
        @yield('content')
        <footer class="main-footer">
            <strong>Copyright &copy; 2024 <a href="{{ route('owner.dashboard') }}">Sugriwa_Subali</a>.</strong> All
            rights reserved.
        </footer>
    </div>

    {{-- Script Java --}}
    <script src="{{ asset('Lte/plugins/chart.js/Chart.min.js') }}"></script>
    <script src="{{ asset('Lte/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('Lte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="{{ asset('Lte/dist/js/adminlte.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('scripts')

</body>

</html>