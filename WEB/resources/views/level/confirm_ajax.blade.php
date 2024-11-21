@empty($level)
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
                <a href="{{ url('/level') }}" class="btn btn-warning btn-block mt-3">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Halaman Level
                </a>
            </div>
        </div>
    </div>
@else
    <form action="{{ url('/level/' . $level->level_id . '/delete_ajax') }}" method="POST" id="form-delete">
        @csrf
        @method('DELETE')
        <div id="modal-master" class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="exampleModalLabel">
                        <i class="fas fa-trash-alt mr-2"></i>Hapus Data Level
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-warning border-left border-warning" style="border-left-width: 5px;">
                        <h5 class="alert-heading mb-2"><i class="icon fas fa-exclamation-triangle mr-2"></i>Konfirmasi
                            Penghapusan</h5>
                        <p class="mb-0">Apakah Anda yakin ingin menghapus data berikut?</p>
                    </div>
                    <div class="modal-body p-4">
                        <table class="table table-hover">
                            <tr>
                                <th class="text-right" style="width: 30%;">Kode Level:</th>
                                <td style="width: 70%;">
                                    <span class="badge badge-primary">{{ $level->level_kode }}</span>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-right">Nama Level:</th>
                                <td>
                                    <strong>{{ $level->level_nama }}</strong>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" data-dismiss="modal" class="btn btn-secondary">
                        <i class="fas fa-times mr-2"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt mr-2"></i>Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    </form>
    <script>
        $(document).ready(function() {
            $("#form-delete").validate({
                rules: {},
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
                                dataLevel.ajax.reload();
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
