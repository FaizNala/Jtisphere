@empty($peran)
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
                    <h5 class="alert-heading mb-2"><i class="icon fas fa-ban mr-2"></i>Data Tidak Ditemukan</h5>
                    <p class="mb-0">Maaf, data yang Anda cari tidak dapat ditemukan dalam sistem kami.</p>
                </div>
                <a href="{{ url('/peran') }}" class="btn btn-warning btn-block mt-3">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Halaman Peran
                </a>
            </div>
        </div>
    </div>
@else
    <form action="{{ url('/peran/' . $peran->peran_id . '/update_ajax') }}" method="POST" id="form-edit">
        @csrf
        @method('PUT')
        <div id="modal-master" class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="exampleModalLabel">
                        <i class="fas fa-pencil-alt mr-2"></i>Edit Data Peran
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label><i class="fas fa-key mr-2"></i>Kode Peran</label>
                        <input value="{{ $peran->peran_kode }}" type="text" name="peran_kode" id="peran_kode"
                            class="form-control" placeholder="Masukkan kode peran" required>
                        <small id="error-peran_kode" class="error-text form-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-tag mr-2"></i>Nama Peran</label>
                        <input value="{{ $peran->peran_nama }}" type="text" name="peran_nama" id="peran_nama"
                            class="form-control" placeholder="Masukkan nama peran" required>
                        <small id="error-nama" class="error-text form-text text-danger"></small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" data-dismiss="modal" class="btn btn-secondary">
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
                    peran_kode: {
                        required: true,
                        minlength: 3,
                        maxlength: 20
                    },
                    peran_nama: {
                        required: true,
                        minlength: 3,
                        maxlength: 100
                    },
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
                                dataPeran.ajax.reload();
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
