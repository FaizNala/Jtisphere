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
                <a href="{{ url('/profile') }}" class="btn btn-warning btn-block">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>
@else
    <form action="{{ url('/profile/update_ajax') }}" method="POST" enctype="multipart/form-data" id="form-edit">
        @csrf
        <div id="modal-master" class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="exampleModalLabel">
                        <i class="fas fa-user-edit mr-2"></i>Edit Profile
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
                                <input type="text" name="username" class="form-control" value="{{ $user->username }}" placeholder="Masukkan username">
                                <small id="error-username" class="error-text form-text text-danger"></small>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-id-card mr-2"></i>Nama</label>
                                <input type="text" name="nama" class="form-control" value="{{ $user->dosen->nama }}" placeholder="Masukkan nama lengkap">
                                <small id="error-nama" class="error-text form-text text-danger"></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-id-badge mr-2"></i>NIP</label>
                                <input type="text" name="nip" class="form-control" value="{{ $user->dosen->nip }}" placeholder="Masukkan NIP">
                                <small id="error-nip" class="error-text form-text text-danger"></small>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-key mr-2"></i>Password</label>
                                <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password baru">
                                <small class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah password</small>
                                <small id="error-password" class="error-text form-text text-danger"></small>
                            </div>
                        </div>
                    </div>

                    <!-- Avatar -->
                    <h6 class="card-subtitle mb-3 mt-4 text-muted"><i class="fas fa-image mr-2"></i>Avatar</h6>
                    <div class="form-group">
                        <label><i class="fas fa-upload mr-2"></i>Upload Avatar</label>
                        <input type="file" name="avatar" class="form-control-file">
                        <small id="error-avatar" class="error-text form-text text-danger"></small>
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
            $("#form-edit").validate({
                rules: {
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
                    avatar: {
                        extension: "jpg|jpeg|png"
                    },
                    password: {
                        minlength: 5
                    }
                },
                submitHandler: function(form) {
                    var formData = new FormData(form);
                    $.ajax({
                        url: form.action,
                        type: form.method,
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.status) {
                                $('#modal-master').modal('hide');
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: response.message
                                });
                                location.reload();
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
