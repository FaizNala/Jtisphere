<form action="{{ url('/kegiatan/ajax') }}" method="POST" id="form-tambah" enctype="multipart/form-data">
    @csrf
    <div id="modal-master" class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="exampleModalLabel">
                    <i class="fas fa-plus-circle mr-2"></i>Tambah Data Kegiatan
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="form-group">
                    <label><i class="fas fa-calendar-alt mr-2"></i>Nama Kegiatan</label>
                    <select name="kegiatan_id" id="kegiatan_id" class="form-control" required>
                        <option value="">Pilih Kegiatan</option>
                        @foreach ($kegiatan as $k)
                            <option value="{{ $k->kegiatan_id }}">{{ $k->kegiatan_nama }}</option>
                        @endforeach
                    </select>
                    <small id="error-kegiatan_id" class="error-text form-text text-danger"></small>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-file-signature mr-2"></i>Nama Agenda</label>
                    <input type="text" name="nama[]" class="form-control" required>
                    <small class="error-text form-text text-danger"></small>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-check-circle mr-2"></i>Status</label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="Belum">Belum</option>
                        <option value="Berjalan">Berjalan</option>
                        <option value="Selesai">Selesai</option>
                    </select>
                    <small id="error-status" class="error-text form-text text-danger"></small>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-calendar-alt mr-2"></i>Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control" required>
                    <small id="error-tanggal_mulai" class="error-text form-text text-danger"></small>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-calendar-check mr-2"></i>Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control" required>
                    <small id="error-tanggal_selesai" class="error-text form-text text-danger"></small>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-users mr-2"></i>Dosen dan Bobot</label>
                    <div id="dosen-bobot-container">
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <select name="dosen[]" class="form-control dosen-select" required>
                                    <option value="">Pilih Dosen</option>
                                    @foreach ($dosen as $d)
                                        <option value="{{ $d->dosen_id }}">{{ $d->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <div class="input-group">
                                    <input type="number" name="bobot[]" class="form-control bobot-input"
                                        placeholder="Bobot (%)" min="0" max="100" step="0.01" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger btn-sm remove-dosen"><i
                                        class="fas fa-times"></i></button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-success btn-sm mt-2" id="add-dosen">
                        <i class="fas fa-plus mr-2"></i>Tambah Dosen
                    </button>
                    <small id="error-dosen" class="error-text form-text text-danger"></small>
                    <small id="error-bobot" class="error-text form-text text-danger"></small>
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
        let dosenCount = 1;

        $('#add-dosen').click(function() {
            dosenCount++;
            let newRow = `
        <div class="row mb-2">
            <div class="col-md-6">
                <select name="dosen[]" class="form-control dosen-select" required>
                    <option value="">Pilih Dosen</option>
                    @foreach ($dosen as $d)
                        <option value="{{ $d->dosen_id }}">{{ $d->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <div class="input-group">
                    <input type="number" name="bobot[]" class="form-control bobot-input"
                        placeholder="Bobot (%)" min="0" max="100" step="0.01" required>
                    <div class="input-group-append">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger btn-sm remove-dosen"><i class="fas fa-times"></i></button>
            </div>
        </div>
        `;
            $('#dosen-bobot-container').append(newRow);
        });

        $(document).on('click', '.remove-dosen', function() {
            if (dosenCount > 1) {
                $(this).closest('.row').remove();
                dosenCount--;
            }
        });

        $("#form-tambah").validate({
            rules: {
                kegiatan_nama: {
                    required: true,
                    minlength: 3,
                    maxlength: 255
                },
                kategori_id: {
                    required: true
                },
                status: {
                    required: true
                },
                deskripsi: { // Tambahkan validasi untuk deskripsi
                    required: true,
                    minlength: 10,
                    maxlength: 1000
                },
                tanggal_mulai: {
                    required: true,
                    date: true
                },
                tanggal_selesai: {
                    required: true,
                    date: true,
                    greaterThan: "#tanggal_mulai"
                },
                'dosen[]': {
                    required: true
                },
                'peran[]': {
                    required: true
                },
                surat_tugas: {
                    extension: "pdf|doc|docx"
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
                            dataKegiatan.ajax.reload();
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
                    },
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
