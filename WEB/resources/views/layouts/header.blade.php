<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                <i class="fas fa-bars"></i>
            </a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ url('/') }}" class="nav-link">Home</a>
        </li>
        {{-- <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ url('/contact') }}" class="nav-link">Contact</a>
        </li> --}}
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <!-- Navbar Search -->
        <li class="nav-item">
            <a class="nav-link" data-widget="navbar-search" href="#" role="button">
                <i class="fas fa-search"></i>
            </a>
            <div class="navbar-search-block">
                <form class="form-inline">
                    <div class="input-group input-group-sm">
                        <input class="form-control form-control-navbar" type="search" placeholder="Search"
                            aria-label="Search">
                        <div class="input-group-append">
                            <button class="btn btn-navbar" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                            <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </li>

        @php
            $unreadNotifications = Auth::user()
                ->notifikasi()
                ->where('is_read', false)
                ->orderBy('created_at', 'desc')
                ->get();
        @endphp

        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-bell"></i>
                @if ($unreadNotifications->count() > 0)
                    <span class="badge badge-warning navbar-badge">{{ $unreadNotifications->count() }}</span>
                @endif
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <span class="dropdown-item dropdown-header">{{ $unreadNotifications->count() }} Unread
                    Notifications</span>
                <div class="dropdown-divider"></div>

                @forelse($unreadNotifications as $notification)
                    <a href="{{ url('/mark-as-read/'. $notification->notifikasi_id) }}" class="dropdown-item mark-as-read">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>{{ $notification->judul }}</strong>
                        <p class="text-sm text-muted">{{ $notification->isi }}</p>
                    </a>
                    <div class="dropdown-divider"></div>
                @empty
                    <span class="dropdown-item text-center text-muted">No Unread Notifications</span>
                @endforelse

                @if ($unreadNotifications->count() > 0)
                    {{-- <a href="{{ route('notifications.index') }}" class="dropdown-item dropdown-footer">
                        See All Notifications
                    </a> --}}
                @endif
            </div>
        </li>

        <!-- Fullscreen Button -->
        <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li>

        <!-- User Info (Avatar and Username) -->
        @if (Auth::check())
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" aria-haspopup="true"
                    aria-expanded="false">
                    <img src="{{ Auth::user()->dosen->avatar ?? asset('default-avatar.png') }}"
                    class="img-circle mr-2" alt="User Avatar"
                    style="width: 30px; height: 30px; object-fit: cover;">
                    @php
                        // Ngambil id current user
                        $currentLevelId = session('current_level_id');
                        // Ngambil nama current user
                        $currentRoleName = optional(
                            Auth::user()->dosen->dosenLevel->where('level_id', $currentLevelId)->first()->level ?? null,
                        )->level_nama;
                    @endphp
                    <!-- Menampilkan -->
                    <span>{{ Auth::user()->username }}@if ($currentRoleName)
                            ({{ $currentRoleName }})
                        @endif
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right shadow" aria-labelledby="navbarDropdown">
                    <div class="dropdown-header text-center bg-primary text-white">
                        <strong>Welcome, {{ Auth::user()->username }}@if ($currentRoleName)
                                ({{ $currentRoleName }})
                            @endif
                        </strong>
                    </div>
                    <div class="dropdown-divider"></div>

                    <!-- Role Switching -->
                    @if (Auth::user()->dosen && Auth::user()->dosen->dosenLevel->isNotEmpty())
                        <div class="dropdown-header text-center">
                            <strong>Available Roles</strong>
                        </div>
                        @foreach (Auth::user()->dosen->dosenLevel as $dosenLevel)
                            <a href="{{ url('/switch-role/' . $dosenLevel->level->level_id) }}" class="dropdown-item">
                                <i class="fas fa-briefcase mr-2"></i> {{ $dosenLevel->level->level_nama }}
                            </a>
                        @endforeach
                        <div class="dropdown-divider"></div>
                    @else
                        <div class="dropdown-header text-center">
                            <strong>No Roles Available</strong>
                        </div>
                        <div class="dropdown-divider"></div>
                    @endif
                    <!-- Logout link -->
                    <a href="{{ url('logout') }}" class="dropdown-item text-danger">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                </div>
            </li>
        @endif
    </ul>
</nav>
