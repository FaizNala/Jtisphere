<div class="sidebar bg-dark">
    <!-- User Info -->
    @if (Auth::check() && Auth::user()->dosen)
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="info">
                <a href="{{ url('/profile') }}" class="d-block" style="text-align: center; font-size: 18px; font-weight: bold;">
                    {{ Str::words(Auth::user()->dosen->nama, 3, '') }}
                </a>
            </div>
        </div>
    @endif

    <!-- SidebarSearch Form -->
    <div class="form-inline mt-2">
        <div class="input-group" data-widget="sidebar-search">
            <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
            <div class="input-group-append">
                <button class="btn btn-sidebar">
                    <i class="fas fa-search fa-fw"></i>
                </button>
            </div>
        </div>
    </div>
    <!-- Sidebar Menu  -->
    <nav class="mt-2">
        @php
            $currentLevelId = session('current_level_id');
            $currentRole = optional(
                optional(Auth::user()->dosen->dosenLevel->where('level_id', $currentLevelId)->first())->level,
            )->level_kode;
        @endphp
        {{-- Admin Sidebar --}}
        @if ($currentRole == 'ADM')
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">
                <!-- Dashboard Link -->
                <li class="nav-item">
                    <a href="{{ url('/') }}" class="nav-link {{ $activeMenu == 'dashboard' ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <!-- Kalender Link -->
                <li class="nav-item">
                    <a href="{{ url('/kalender') }}" class="nav-link {{ $activeMenu == 'kalender' ? 'active' : '' }}">
                        <i class="nav-icon fas fa-calendar-alt"></i>
                        <p>Kalender</p>
                    </a>
                </li>
                <!-- Data Pengguna Section -->
                <li class="nav-header">Data Pengguna</li>
                <li class="nav-item">
                    <a href="{{ url('/user') }}" class="nav-link {{ $activeMenu == 'user' ? 'active' : '' }}">
                        <i class="nav-icon far fa-user"></i>
                        <p>Data Pengguna</p>
                    </a>
                </li>
                <!-- Data Kegiatan Section -->
                <li class="nav-header">Data Kegiatan</li>
                <li
                    class="nav-item has-treeview {{ in_array($activeMenu, ['kategori', 'periode', 'peran', 'kegiatan']) ? 'menu-open' : '' }}">
                    <a href="#"
                        class="nav-link {{ in_array($activeMenu, ['kategori', 'periode', 'peran', 'kegiatan']) ? 'active' : '' }}">
                        <i class="nav-icon fas fa-folder"></i>
                        <p>
                            Data Kegiatan
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ url('/kategori') }}"
                                class="nav-link {{ $activeMenu == 'kategori' ? 'active' : '' }}">
                                <i class="nav-icon far fa-bookmark"></i>
                                <p>Kategori Kegiatan</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('/periode') }}"
                                class="nav-link {{ $activeMenu == 'periode' ? 'active' : '' }}">
                                <i class="nav-icon fas fa-calendar-alt"></i>
                                <p>Periode Kegiatan</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('/peran') }}"
                                class="nav-link {{ $activeMenu == 'peran' ? 'active' : '' }}">
                                <i class="nav-icon fas fa-user-tie"></i>
                                <p>Peran Kegiatan</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('/kegiatan') }}"
                                class="nav-link {{ $activeMenu == 'kegiatan' ? 'active' : '' }}">
                                <i class="nav-icon far fa-list-alt"></i>
                                <p>Data Kegiatan</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-header">Statistik</li>
                <li class="nav-item">
                    <a href="{{ url('/statistik') }}"
                        class="nav-link {{ $activeMenu == 'statistik' ? 'active' : '' }}">
                        <i class="nav-icon fas fa-chart-line"></i>
                        <p>Statistik Kegiatan</p>
                    </a>
                </li>
            </ul>
        @endif
        {{-- Pimpinan Sidebar --}}
        @if ($currentRole == 'PMN')
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">
                <!-- Dashboard Link -->
                <li class="nav-item">
                    <a href="{{ url('/') }}" class="nav-link {{ $activeMenu == 'dashboard' ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <!-- Kalender Link -->
                <li class="nav-item">
                    <a href="{{ url('/kalender') }}" class="nav-link {{ $activeMenu == 'kalender' ? 'active' : '' }}">
                        <i class="nav-icon fas fa-calendar-alt"></i>
                        <p>Kalender</p>
                    </a>
                </li>
                <!-- Data Pengguna Section -->
                <li class="nav-header">Data Pengguna</li>
                <li class="nav-item">
                    <a href="{{ url('/user') }}" class="nav-link {{ $activeMenu == 'user' ? 'active' : '' }}">
                        <i class="nav-icon far fa-user"></i>
                        <p>Data Pengguna</p>
                    </a>
                </li>
                <!-- Data Kegiatan Section -->
                <li class="nav-header">Data Kegiatan</li>
                <li class="nav-item">
                    <a href="{{ url('/kegiatan') }}" class="nav-link {{ $activeMenu == 'kegiatan' ? 'active' : '' }}">
                        <i class="nav-icon far fa-list-alt"></i>
                        <p>Data Kegiatan</p>
                    </a>
                </li>
                <li class="nav-header">Statistik</li>
                <li class="nav-item">
                    <a href="{{ url('/statistik') }}"
                        class="nav-link {{ $activeMenu == 'statistik' ? 'active' : '' }}">
                        <i class="nav-icon fas fa-chart-line"></i>
                        <p>Statistik Kegiatan</p>
                    </a>
                </li>
            </ul>
        @endif

        {{-- Dosen Sidebar --}}
        @if ($currentRole == 'DSN')
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">
                <!-- Dashboard Link -->
                <li class="nav-item">
                    <a href="{{ url('/') }}" class="nav-link {{ $activeMenu == 'dashboard' ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <!-- Kalender Link -->
                <li class="nav-item">
                    <a href="{{ url('/kalender') }}"
                        class="nav-link {{ $activeMenu == 'kalender' ? 'active' : '' }}">
                        <i class="nav-icon fas fa-calendar-alt"></i>
                        <p>Kalender</p>
                    </a>
                </li>

                <!-- Kegiatan Link -->
                <li class="nav-header">Data Kegiatan</li>
                <li
                    class="nav-item has-treeview {{ in_array($activeMenu, ['kegiatan', 'agenda_dosen']) ? 'menu-open' : '' }}">
                    <a href="#"
                        class="nav-link {{ in_array($activeMenu, ['kegiatan', 'agenda_dosen']) ? 'active' : '' }}">
                        <i class="nav-icon fas fa-folder"></i>
                        <p>
                            Data Kegiatan
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ url('/kegiatan_dosen') }}"
                                class="nav-link {{ $activeMenu == 'kegiatan' ? 'active' : '' }}">
                                <i class="nav-icon fas fa-list-alt"></i>
                                <p>Kegiatan</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('/agenda_dosen') }}"
                                class="nav-link {{ $activeMenu == 'agenda_dosen' ? 'active' : '' }}">
                                <i class="nav-icon fas fa-clipboard-list"></i>
                                <p>Agenda Dosen</p>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        @endif
    </nav>
</div>
