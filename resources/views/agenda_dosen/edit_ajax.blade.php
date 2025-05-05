@empty($agenda)
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
                <a href="{{ url('/kegiatan_dosen') }}" class="btn btn-warning btn-block mt-3">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Halaman Kegiatan Dosen
                </a>
            </div>
        </div>
    </div>
@else
    <form action="{{ url('/agenda_dosen/' . $agenda->agenda_id . '/update_ajax') }}" method="POST" id="form-edit" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div id="modal-master" class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="exampleModalLabel">
                        <i class="fas fa-pencil-alt mr-2"></i>Edit Data Agenda Kegiatan
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="kegiatan_id" value="{{ $agenda->kegiatan_id }}">
                    <div class="form-group">
                        <label><i class="fas fa-check-circle mr-2"></i>Status</label>
                        <select name="status" class="form-control" required>
                            <option value="Belum" {{ ($agenda->status ?? '') == 'Belum' ? 'selected' : '' }}>Belum</option>
                            <option value="Berjalan" {{ ($agenda->status ?? '') == 'Berjalan' ? 'selected' : '' }}>Berjalan</option>
                            <option value="Selesai" {{ ($agenda->status ?? '') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                        </select>
                        <small id="error-status" class="error-text form-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-file-upload mr-2"></i>Bukti Agenda</label>
                        <input type="file" name="bukti_agenda" id="bukti_agenda" class="form-control-file">
                        <small id="error-bukti_agenda" class="error-text form-text text-danger"></small>

                        @if (!$buktiAgenda->isEmpty())
                            @foreach($buktiAgenda as $dokumen)
                                <p class="mt-2">File saat ini: {{ $dokumen->dokumen_nama }}</p>
                            @endforeach
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remove_bukti_agenda" id="remove_bukti_agenda">
                                <label class="form-check-label" for="remove_bukti_agenda">
                                    Hapus bukti agenda saat ini
                                </label>
                            </div>
                        @endif
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
                    status: {
                        required: true
                    },
                    bukti_agenda: {
                        extension: "pdf|doc|docx|jpg|png|jpeg"
                    }
                },
                messages: {
                    bukti_agenda: {
                        extension: "Hanya file PDF, DOC, DOCX, JPG, PNG, JPEG yang diperbolehkan"
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
                                $('#myModal').modal('hide');
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: response.message
                                });
                                dataAgenda.ajax.reload();
                            } else {
                                $('.error-text').text('');
                                $.each(response.errors, function(prefix, val) {
                                    $('#error-' + prefix).text(val[0]);
                                });
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Terjadi Kesalahan',
                                    text: response.message
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Kesalahan Server',
                                text: 'Terjadi kesalahan pada server'
                            });
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
