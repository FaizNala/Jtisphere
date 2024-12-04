@empty($periode)
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
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Halaman Periode
                </a>
            </div>
        </div>
    </div>
@else
    <form action="{{ url('/periode/' . $periode->periode_id . '/update_ajax') }}" method="POST" id="form-edit">
        @csrf
        @method('PUT')
        <div id="modal-master" class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="exampleModalLabel">
                        <i class="fas fa-pencil-alt mr-2"></i>Edit Data Periode
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label><i class="fas fa-calendar mr-2"></i>Periode</label>
                        <input value="{{ $periode->periode }}" type="text" name="periode" id="periode"
                            class="form-control" placeholder="Masukkan nama periode" required>
                        <small id="error-periode" class="error-text form-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-calendar-alt mr-2"></i>Tanggal Mulai</label>
                        <input value="{{ $periode->tanggal_mulai }}" type="date" name="tanggal_mulai" id="tanggal_mulai"
                            class="form-control" required>
                        <small id="error-tanggal_mulai" class="error-text form-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-calendar-alt mr-2"></i>Tanggal Selesai</label>
                        <input value="{{ $periode->tanggal_selesai }}" type="date" name="tanggal_selesai"
                            id="tanggal_selesai" class="form-control" required>
                        <small id="error-tanggal_selesai" class="error-text form-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-toggle-on mr-2"></i>Status</label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="Aktif" {{ $periode->status == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="Tidak Aktif" {{ $periode->status == 'Tidak Aktif' ? 'selected' : '' }}>Tidak
                                Aktif</option>
                        </select>
                        <small id="error-status" class="error-text form-text text-danger"></small>
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
                    periode: {
                        required: true,
                        minlength: 3,
                    },
                    tanggal_mulai: {
                        required: true,
                    },
                    tanggal_selesai: {
                        required: true,
                    },
                    status: {
                        required: true,
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
                                dataPeriode.ajax.reload();
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
