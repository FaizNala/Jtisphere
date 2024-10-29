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
                    <h5 class="alert-heading mb-2"><i class="icon fas fa-ban mr-2"></i>Kesalahan!!!</h5>
                    Data yang Anda cari tidak ditemukan.
                </div>
                <a href="{{ url('/user') }}" class="btn btn-warning btn-block">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>
@else
    <form action="{{ url('/user/' . $user->user_id . '/update_ajax') }}" method="POST" id="form-edit">
        @csrf
        @method('PUT')
        <div id="modal-master" class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="exampleModalLabel">
                        <i class="fas fa-user-edit mr-2"></i>Edit Data User
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <!-- Informasi Dasar -->
                    <h6 class="card-subtitle mb-3 text-muted"><i class="fas fa-info-circle mr-2"></i>Informasi Dasar</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-user mr-2"></i>Username</label>
                                <input type="text" name="username" id="username" class="form-control" value="{{ $user->username }}" placeholder="Masukkan username">
                                <small id="error-username" class="error-text form-text text-danger"></small>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-lock mr-2"></i>Password</label>
                                <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password">
                                <small class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah password</small>
                                <small id="error-password" class="error-text form-text text-danger"></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-id-card mr-2"></i>Nama</label>
                                <input type="text" name="nama" id="nama" class="form-control" value="{{ $user->nama }}" placeholder="Masukkan nama lengkap">
                                <small id="error-nama" class="error-text form-text text-danger"></small>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-id-badge mr-2"></i>NIP</label>
                                <input type="text" name="nip" id="nip" class="form-control" value="{{ $user->nip }}" placeholder="Masukkan NIP">
                                <small id="error-nip" class="error-text form-text text-danger"></small>
                            </div>
                        </div>
                    </div>

                    <!-- Level Pengguna -->
                    <h6 class="card-subtitle mb-3 mt-4 text-muted"><i class="fas fa-layer-group mr-2"></i>Level Pengguna</h6>
                    <div class="form-group mt-3">
                        <label><i class="fas fa-users-cog mr-2"></i>Level Pengguna</label>
                        <select name="level[]" id="level" class="form-control select2-multiple" multiple="multiple"
                            data-placeholder="Pilih Level Pengguna">
                            @foreach ($allLevels as $l)
                                <option value="{{ $l->level_id }}" {{ in_array($l->level_id, $userLevels) ? 'selected' : '' }}>
                                    {{ $l->level_nama }}
                                </option>
                            @endforeach
                        </select>
                        <small id="error-level" class="error-text form-text text-danger"></small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>Simpan
                    </button>
                </div>
            </div>
        </div>
    </form>
    <script>
        $(document).ready(function() {
            $('#level').select2({
                placeholder: "Pilih Level Pengguna",
                allowClear: true,
                width: '100%'
            });

            $("#form-edit").validate({
                rules: {
                    'level[]': {
                        required: true
                    },
                    username: {
                        required: true,
                        minlength: 3,
                        maxlength: 20
                    },
                    nama: {
                        required: true,
                        minlength: 3,
                        maxlength: 50
                    },
                    nip: {
                        required: true,
                        digits: true,
                        minlength: 15,
                        maxlength: 25
                    },
                    password: {
                        minlength: 5,
                        maxlength: 20
                    }
                },
                submitHandler: function(form) {
                    $.ajax({
                        url: form.action,
                        type: form.method,
                        data: $(form).serialize(),
                        success: function(response) {
                            if (response.status) {
                                $('#myModal').modal('hide');
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: response.message
                                });
                                dataUser.ajax.reload();
                            } else {
                                $('.error-text').text('');
                                $.each(response.msgField, function(prefix, val) {
                                    $('#error-' + prefix).text(val[0]);
                                });
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Terjadi Kesalahan',
                                    text: response.message
                                });
                            }
                        }
                    });
                    return false;
                },
                errorElement: 'span',
                errorPlacement: function(error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('.form-group').append(error);
                },
                highlight: function(element, errorClass, validClass) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function(element, errorClass, validClass) {
                    $(element).removeClass('is-invalid');
                }
            });
        });
    </script>
@endempty
