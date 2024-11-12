<div class="sidebar bg-dark">
    <!-- User Info -->
    @if(Auth::check() && Auth::user()->dosen)
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
            <img src="{{ Auth::user()->dosen->avatar ? asset('storage/avatars/' . Auth::user()->dosen->avatar) : asset('default-avatar.png') }}"
                 class="rounded-circle"
                 alt="User Image">
        </div>
        <div class="info">
            <a href="{{ url('/profile') }}" class="d-block">
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
    <!-- Sidebar Menu -->

    {{-- Admin Sidebar --}}
    @if(auth()->user()->dosen->dosenLevel->first()->level->level_kode == 'ADM')
    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <!-- Dashboard Link -->
            <li class="nav-item">
                <a href="{{ url('/') }}" class="nav-link {{ $activeMenu == 'dashboard' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-tachometer-alt"></i>
                    <p>Dashboard</p>
                </a>
            </li>
            <!-- Data Pengguna Section -->
            <li class="nav-header">Data Pengguna</li>
            <li class="nav-item">
                <a href="{{ url('/level') }}" class="nav-link {{ $activeMenu == 'level' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-layer-group"></i>
                    <p>Level Pengguna</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/user') }}" class="nav-link {{ $activeMenu == 'user' ? 'active' : '' }}">
                    <i class="nav-icon far fa-user"></i>
                    <p>Data Pengguna</p>
                </a>
            </li>
            <!-- Data Kegiatan Section -->
            <li class="nav-header">Data Kegiatan</li>
            <li class="nav-item">
                <a href="{{ url('/kategori') }}" class="nav-link {{ $activeMenu == 'kategori' ? 'active' : '' }}">
                    <i class="nav-icon far fa-bookmark"></i>
                    <p>Kategori Kegiatan</p>
                </a>
            </li>
            <!-- Jabatan Kegiatan Section -->
            <li class="nav-item">
                <a href="{{ url('/peran') }}" class="nav-link {{ $activeMenu == 'peran' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-user-tie"></i>
                    <p>Peran Kegiatan</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/kegiatan') }}" class="nav-link {{ $activeMenu == 'kegiatan' ? 'active' : '' }}">
                    <i class="nav-icon far fa-list-alt"></i>
                    <p>Data Kegiatan</p>
                </a>
            </li>
            {{-- <!-- Data Transaksi Section -->
            <li class="nav-header">Dokumen</li>
            <li class="nav-item">
                <a href="{{ url('/stok') }}" class="nav-link {{ $activeMenu == 'dokumen' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-cubes"></i>
                    <p>Dokumen Kegiatan</p>
                </a>
            </li> --}}
        </ul>
        @endif

        {{-- Pimpinan Sidebar --}}
        @if(auth()->user()->dosen->dosenLevel->first()->level->level_kode == 'PMN')
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <!-- Dashboard Link -->
            <li class="nav-item">
                <a href="{{ url('/') }}" class="nav-link {{ $activeMenu == 'dashboard' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-tachometer-alt"></i>
                    <p>Dashboard</p>
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
        </ul>
        @endif

        {{-- Dosen Sidebar --}}
        @if(auth()->user()->dosen->dosenLevel->first()->level->level_kode == 'DSN')
        @endif
    </nav>
</div>
