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
    <form action="{{ url('/kegiatan_dosen/' . $agenda->agenda_id . '/update_agenda_ajax') }}" method="POST" id="form-edit">
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
                        <label><i class="fas fa-file-signature mr-2"></i>Nama Agenda</label>
                        <input value="{{ $agenda->nama }}" type="text" name="nama" class="form-control" required>
                        <small id="error-nama" class="error-text form-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-check-circle mr-2"></i>Status</label>
                        <select name="status" class="form-control" required>
                            <option value="Belum" {{ $agenda->status == 'Belum' ? 'selected' : '' }}>Belum</option>
                            <option value="Berjalan" {{ $agenda->status == 'Berjalan' ? 'selected' : '' }}>Berjalan</option>
                            <option value="Selesai" {{ $agenda->status == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                        </select>
                        <small id="error-status" class="error-text form-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-calendar-alt mr-2"></i>Tanggal Mulai</label>
                        <input value="{{ $agenda->tanggal_mulai }}" type="date" name="tanggal_mulai" class="form-control" required>
                        <small id="error-tanggal_mulai" class="error-text form-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-calendar-check mr-2"></i>Tanggal Selesai</label>
                        <input value="{{ $agenda->tanggal_selesai }}" type="date" name="tanggal_selesai" class="form-control" required>
                        <small id="error-tanggal_selesai" class="error-text form-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-users mr-2"></i>Dosen</label>
                        <div id="dosen-container">
                            @foreach ($agenda->dosen as $dosen)
                                <div class="row mb-2">
                                    <div class="col-md-11">
                                        <select name="dosen[]" class="form-control dosen-select" required>
                                            <option value="">Pilih Dosen</option>
                                            @foreach ($dosenList as $d)
                                                <option value="{{ $d->dosen_id }}" {{ $dosen->dosen_id == $d->dosen_id ? 'selected' : '' }}>
                                                    {{ $d->nama }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-danger btn-sm remove-dosen"><i class="fas fa-times"></i></button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-success btn-sm mt-2" id="add-dosen">
                            <i class="fas fa-plus mr-2"></i>Tambah Dosen
                        </button>
                        <small id="error-dosen" class="error-text form-text text-danger"></small>
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
            let dosenCount = {{ count($agenda->dosen) }};

            $('#add-dosen').click(function() {
                dosenCount++;
                let newRow = `
                    <div class="row mb-2">
                        <div class="col-md-11">
                            <select name="dosen[]" class="form-control dosen-select" required>
                                <option value="">Pilih Dosen</option>
                                @foreach ($dosenList as $d)
                                    <option value="{{ $d->dosen_id }}">{{ $d->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-danger btn-sm remove-dosen"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                `;
                $('#dosen-container').append(newRow);
            });

            $(document).on('click', '.remove-dosen', function() {
                if (dosenCount > 1) {
                    $(this).closest('.row').remove();
                    dosenCount--;
                }
            });

            $("#form-edit").validate({
                rules: {
                    nama: {
                        required: true,
                        minlength: 3,
                        maxlength: 255
                    },
                    status: {
                        required: true
                    },
                    tanggal_mulai: {
                        required: true,
                        date: true
                    },
                    tanggal_selesai: {
                        required: true,
                        date: true,
                    },
                    'dosen[]': {
                        required: true
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
