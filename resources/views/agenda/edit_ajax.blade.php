@empty($kegiatan)
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
                    <p class="mb-0">Maaf, data kegiatan yang Anda cari tidak dapat ditemukan dalam sistem kami.</p>
                </div>
                <a href="{{ url('/kegiatan') }}" class="btn btn-warning btn-block mt-3">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Halaman Kegiatan
                </a>
            </div>
        </div>
    </div>
@else
    <form action="{{ url('/agenda/'.$kegiatan->kegiatan_id.'/update_ajax') }}" method="POST" id="form-edit" enctype="multipart/form-data">
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
                    <div id="agenda-container">
                        <div class="button mb-3">
                            <button type="button" class="btn btn-success btn-sm" id="add-agenda">
                                <i class="fas fa-plus mr-2"></i>Tambah Agenda
                            </button>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-calendar-alt mr-2"></i>Nama Kegiatan</label>
                            <input type="text" name="kegiatan_nama" id="kegiatan_nama" class="form-control"
                                placeholder="Masukkan nama kegiatan" value="{{ $kegiatan->kegiatan_nama }}" disabled>
                        </div>

                        @foreach ($agenda as $index => $a)
                            <div class="agenda-item mb-3">
                                <input type="hidden" name="agenda_id[]" value="{{ $a->agenda_id }}">
                                <div class="form-group">
                                    <label><i class="fas fa-file-signature mr-2"></i>Nama Agenda</label>
                                    <input type="text" name="nama[]" class="form-control" required value="{{ $a->nama }}">
                                </div>

                                <div class="form-group">
                                    <label><i class="fas fa-check-circle mr-2"></i>Status</label>
                                    <select name="status[]" class="form-control" required>
                                        <option value="Belum" {{ $a->kegiatanAgenda->status == 'Belum' ? 'selected' : '' }}>Belum</option>
                                        <option value="Berjalan" {{ $a->kegiatanAgenda->status == 'Berjalan' ? 'selected' : '' }}>Berjalan</option>
                                        <option value="Selesai" {{ $a->kegiatanAgenda->status == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label><i class="fas fa-calendar-alt mr-2"></i>Tanggal Mulai</label>
                                    <input type="date" name="tanggal_mulai[]" class="form-control" required value="{{ $a->tanggal_mulai }}">
                                </div>

                                <div class="form-group">
                                    <label><i class="fas fa-calendar-check mr-2"></i>Tanggal Selesai</label>
                                    <input type="date" name="tanggal_selesai[]" class="form-control" required value="{{ $a->tanggal_selesai }}">
                                </div>

                                <div class="form-group">
                                    <label><i class="fas fa-users mr-2"></i>Dosen dan Bobot</label>
                                    <div class="dosen-bobot-container">
                                        @foreach ($a->agendaDosen as $ad)
                                            <div class="row mb-2">
                                                <div class="col-md-6">
                                                    <select name="dosen[]" class="form-control dosen-select" required>
                                                        <option value="">Pilih Dosen</option>
                                                        @foreach ($dosen as $d)
                                                            <option value="{{ $d->dosen_id }}"
                                                                {{ $ad->dosen_id == $d->dosen_id ? 'selected' : '' }}>
                                                                {{ $d->nama }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="number" name="bobot[]" class="form-control" placeholder="Bobot" required value="{{ $ad->bobot }}">
                                                </div>
                                                <div class="col-md-1">
                                                    <button type="button" class="btn btn-danger btn-sm remove-dosen"><i class="fas fa-times"></i></button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="button" class="btn btn-success btn-sm mt-2 add-dosen">
                                        <i class="fas fa-plus mr-2"></i>Tambah Dosen
                                    </button>
                                </div>

                                <button type="button" class="btn btn-danger btn-sm mt-2 remove-agenda">
                                    <i class="fas fa-trash mr-2"></i>Hapus Agenda
                                </button>
                            </div>
                        @endforeach
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
        let agendaCount = {{ count($agenda ) }};

        $('#add-agenda').click(function() {
            agendaCount++;
            let newRow = `
                <div class="agenda-item mb-3">
                    <div class="form-group">
                        <label><i class="fas fa-file-signature mr-2"></i>Nama Agenda</label>
                        <input type="text" name="nama[]" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-check-circle mr-2"></i>Status</label>
                        <select name="status[]" class="form-control" required>
                            <option value="Belum">Belum</option>
                            <option value="Berjalan">Berjalan</option>
                            <option value="Selesai">Selesai</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-calendar-alt mr-2"></i>Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai[]" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-calendar-check mr-2"></i>Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai[]" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-users mr-2"></i>Dosen dan Bobot</label>
                        <div class="dosen-bobot-container">
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
                                    <input type="number" name="bobot[]" class="form-control" placeholder="Bobot" required>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-danger btn-sm remove-dosen"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-success btn-sm mt-2 add-dosen">
                            <i class="fas fa-plus mr-2"></i>Tambah Dosen
                        </button>
                    </div>

                    <button type="button" class="btn btn-danger btn-sm mt-2 remove-agenda">
                        <i class="fas fa-trash mr-2"></i>Hapus Agenda
                    </button>
                </div>
            `;
            $('#agenda-container').append(newRow);
        });

        $(document).on('click', '.add-dosen', function() {
            let newDosenRow = `
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
                        <input type="number" name="bobot[]" class="form-control" placeholder="Bobot" required>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger btn-sm remove-dosen"><i class="fas fa-times"></i></button>
                    </div>
                </div>
            `;
            $(this).siblings('.dosen-bobot-container').append(newDosenRow);
        });

        $(document).on('click', '.remove-dosen', function() {
            $(this).closest('.row').remove();
        });

        $(document).on('click', '.remove-agenda', function() {
            $(this).closest('.agenda-item').remove();
        });

        $("#form-edit").validate({
            rules: {
                'nama[]': {
                    required: true,
                    minlength: 3,
                    maxlength: 255
                },
                'status[]': {
                    required: true
                },
                'tanggal_mulai[]': {
                    required: true,
                    date: true
                },
                'tanggal_selesai[]': {
                    required: true,
                    date: true,
                },
                'dosen[]': {
                    required: true
 },
                'bobot[]': {
                    required: true,
                    min: 0,
                    max: 100
                }
            },
            submitHandler: function(form) {
                var formData = new FormData(form);
                $.ajax({
                    url: form.action,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Terjadi Kesalahan',
                            text: xhr.responseJSON.message
                        });
                    }
                });
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
