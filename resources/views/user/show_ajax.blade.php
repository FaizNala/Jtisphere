@empty($user)
    <div id="modal-master" class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white">
                    <i class="fas fa-exclamation-circle mr-2"></i>Data Tidak Ditemukan
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center p-5">
                <i class="fas fa-user-times error-icon mb-3"></i>
                <p class="mb-4">Data user yang Anda cari tidak tersedia</p>
                <a href="{{ url('/user') }}" class="btn btn-danger">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>
@else
    <div id="modal-master" class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white">
                    Detail User
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="user-profile">
                    <div class="profile-header">
                        <div class="avatar">
                            {{ strtoupper(substr($user->nama, 0, 1)) }}
                        </div>
                        <h4>{{ $user->nama }}</h4>
                        <p class="text-muted">{{ $user->nip }}</p>
                        <div class="user-level">
                            @foreach(explode(', ', $user->level_nama) as $level)
                                <span class="level-badge">{{ $level }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div class="info-section">
                        <div class="info-item">
                            <label>Username</label>
                            <span>{{ $user->username }}</span>
                        </div>
                        <div class="info-item">
                            <label>Status</label>
                            <span class="status-active">
                                <i class="fas fa-check-circle mr-1"></i>Aktif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Tutup
                </button>
                <button type="button" class="btn btn-primary" onclick="modalAction('{{ url('/user/' . $user->user_id . '/edit_ajax') }}')">
                    Edit
                </button>
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

        .avatar {
            width: 80px;
            height: 80px;
            background: #007bff;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            margin: 0 auto 15px;
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
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .modal-header {
            border-radius: 8px 8px 0 0;
        }
    </style>
@endempty
