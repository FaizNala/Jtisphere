@empty($user)
    <div id="modal-master" class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="exampleModalLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Kesalahan
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-danger border-left border-danger" style="border-left-width: 5px;">
                    <h5 class="alert-heading"><i class="icon fas fa-ban mr-2"></i>Data Tidak Ditemukan</h5>
                    <p class="mb-0">Maaf, data yang Anda cari tidak dapat ditemukan dalam sistem kami.</p>
                </div>
                <a href="{{ url('/statistik') }}" class="btn btn-warning btn-block mt-3">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Halaman Pengguna
                </a>
            </div>
        </div>
    </div>
@else
    <div id="modal-master" class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="exampleModalLabel">
                    <i class="fas fa-info-circle mr-2"></i>Statistik Beban Kerja Dosen
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="user-profile">
                    <div class="profile-header">
                        <div class="avatar">
                            @if ($user->avatar)
                                <img src="{{ asset('storage/avatars/' . $user->avatar) }}"
                                    alt="{{ $user->nama }}'s Avatar" class="img-fluid rounded-circle"
                                    style="width: 150px; height: 150px;">
                            @else
                                <img src="{{ asset('default-avatar.png') }}" alt="Default Avatar"
                                    class="img-fluid rounded-circle" style="width: 150px; height: 150px;">
                            @endif
                        </div>
                        <h4>{{ $user->nama }}</h4>
                        <p class="text-muted">{{ $user->nip }}</p>
                        <div class="user-level">
                            @foreach (explode(', ', $user->level_nama) as $level)
                                <span class="level-badge">{{ $level }}</span>
                            @endforeach
                        </div>
                    </div>

                    {{-- Menampilkan total bobot di bagian profil pengguna --}}
                    @if ($dosenKegiatan->isNotEmpty())
                        <div class="mt-3">
                            <h5 class="card-text text-center">Beban Kerja</h5>
                            <h2 class="card-text {{ $dosenKegiatan->sum('bobot') > 20 ? 'text-danger' : 'text-primary' }} font-weight-bold text-center"
                                style="margin: 0;">
                                {{ $dosenKegiatan->sum('bobot') }}
                            </h2>
                        </div>
                    @else
                        <div class="mt-3">
                            <h5 class="card-text text-center">Beban Kerja</h5>
                            <h2 class="card-text {{ $dosenKegiatan->sum('bobot') > 20 ? 'text-danger' : 'text-primary' }} font-weight-bold text-center"
                                style="margin: 0;">
                                0
                            </h2>
                        </div>
                    @endif
                </div>

                <table class="table table-hover mt-3">
                    <thead class="bg-info">
                        <tr>
                            <th>No</th>
                            <th>Kegiatan</th>
                            <th>Peran</th>
                            <th>Bobot</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dosenKegiatan as $item)
                            <tr>
                                <td>{{ $loop->index + 1 }}</td>
                                <td>{{ $item->kegiatan->kegiatan_nama }}</td>
                                <td>{{ $item->peran->peran_nama }}</td>
                                <td>{{ $item->bobot }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Tidak ada kegiatan yang terdaftar .</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" data-dismiss="modal" class="btn btn-secondary">
                    <i class="fas fa-times mr-2"></i>Tutup
                </button>
                <a href="{{ url('/statistik/' . $user->user_id . '/export_statistik') }}" class="btn btn-warning mr-2">
                    <i class="fas fa-file-pdf mr-2"></i>Export Statistik
                </a>
            </div>
        </div>
    </div>
    <style>
        .error-icon {
            font-size: 4rem;
            color: #dc3545;
            display: block;
        }

        .user-profile {
            padding: 20px;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .user-level {
            margin-top: 10px;
        }

        .level-badge {
            background: #e3f2fd;
            color: #007bff;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 0.9rem;
            margin: 0 5px;
            display: inline-block;
        }

        .info-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-item label {
            color: #6c757d;
            margin-bottom: 0;
        }

        .status-active {
            color: #28a745;
            font-weight: 500;
        }

        .modal-content {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            border-radius: 8px 8px 0 0;
        }
    </style>
@endempty
