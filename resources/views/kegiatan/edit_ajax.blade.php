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
    <form action="{{ url('/kegiatan/' . $kegiatan->kegiatan_id . '/update_ajax') }}" method="POST" id="form-edit"
        enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div id="modal-master" class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="exampleModalLabel">
                        <i class="fas fa-pencil-alt mr-2"></i>Edit Data Kegiatan
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label><i class="fas fa-calendar-alt mr-2"></i>Nama Kegiatan</label>
                        <input type="text" name="kegiatan_nama" id="kegiatan_nama" class="form-control"
                            placeholder="Masukkan nama kegiatan" value="{{ $kegiatan->kegiatan_nama }}" required>
                        <small id="error-kegiatan_nama" class="error-text form-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-tags mr-2"></i>Kategori</label>
                        <select name="kategori_id" id="kategori_id" class="form-control" required>
                            <option value="">Pilih Kategori</option>
                            @foreach ($kategori as $k)
                                <option value="{{ $k->kategori_id }}"
                                    {{ $kegiatan->kategori_id == $k->kategori_id ? 'selected' : '' }}>
                                    {{ $k->kategori_nama }}
                                </option>
                            @endforeach
                        </select>
                        <small id="error-kategori_id" class="error-text form-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-check-circle mr-2"></i>Status</label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="Belum" {{ $kegiatan->status == 'Belum' ? 'selected' : '' }}>Belum</option>
                            <option value="Berjalan" {{ $kegiatan->status == 'Berjalan' ? 'selected' : '' }}>Berjalan
                            </option>
                            <option value="Selesai" {{ $kegiatan->status == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                        </select>
                        <small id="error-status" class="error-text form-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-align-left mr-2"></i>Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3" placeholder="Masukkan deskripsi kegiatan"
                            required>{{ $kegiatan->deskripsi }}</textarea>
                        <small id="error-deskripsi" class="error-text form-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-calendar-alt mr-2"></i>Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control"
                               value="{{ $kegiatan->tanggal_mulai }}" required>
                        <small id="error-tanggal_mulai" class="error-text form-text text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-calendar-check mr-2"></i>Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control"
                               value="{{ $kegiatan->tanggal_selesai }}" required>
                        <small id="error-tanggal_selesai" class="error-text form-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-users mr-2"></i>Dosen dan Peran</label>
                        <div id="dosen-peran-container">
                            @foreach ($dosenKegiatan as $index => $dk)
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <select name="dosen[]"
                                            class="form-control dosen-select" required>
                                            <option value="">Pilih Dosen</option>
                                            @foreach ($dosen as $d)
                                                <option value="{{ $d->dosen_id }}"
                                                    {{ $dk->dosen_id == $d->dosen_id ? 'selected' : '' }}>
                                                    {{ $d->nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <select name="peran[]"
                                            class="form-control peran-select" required>
                                            <option value="">Pilih Peran</option>
                                            @foreach ($peran as $p)
                                                <option value="{{ $p->peran_id }}"
                                                    {{ $dk->peran_id == $p->peran_id ? 'selected' : '' }}>
                                                    {{ $p->peran_nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-danger btn-sm remove-dosen"><i
                                                class="fas fa-times"></i></button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-success btn-sm mt-2" id="add-dosen">
                            <i class="fas fa-plus mr-2"></i>Tambah Dosen
                        </button>
                        <small id="error-dosen" class="error-text form-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-file-upload mr-2"></i>Surat Tugas</label>
                        <input type="file" name="surat_tugas" id="surat_tugas" class="form-control-file">
                        <small id="error-surat_tugas" class="error-text form-text text-danger"></small>
                        @if ($kegiatan->suratTugas && $kegiatan->suratTugas->dokumen)
                            <p class="mt-2">File saat ini: {{ $kegiatan->suratTugas->dokumen->dokumen_nama }}</p>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remove_surat_tugas"
                                    id="remove_surat_tugas">
                                <label class="form-check-label" for="remove_surat_tugas">
                                    Hapus surat tugas saat ini
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
            let dosenCount = {{ count($kegiatan->dosenKegiatan) }};

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
                    <select name="peran[]" class="form-control peran-select" required>
                        <option value="">Pilih Peran</option>
                        @foreach ($peran as $p)
                            <option value="{{ $p->peran_id }}">{{ $p->peran_nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-sm remove-dosen"><i class="fas fa-times"></i></button>
                </div>
            </div>
            `;
                $('#dosen-peran-container').append(newRow);
            });

            $(document).on('click', '.remove-dosen', function() {
                if (dosenCount > 1) {
                    $(this).closest('.row').remove();
                    dosenCount--;
                }
            });

            $("#form-edit").validate({
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
                    deskripsi: {
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
                messages: {
                    'dosen[]': {
                        required: "Pilih setidaknya satu dosen"
                    },
                    'peran[]': {
                        required: "Pilih peran untuk setiap dosen"
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
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        dataKegiatan.ajax.reload();
                                    }
                                });
                            } else {
                                $('.error-text').text('');
                                if (response.errors) {
                                    $.each(response.errors, function(key, value) {
                                        $('#error-' + key).text(value[0]);
                                    });
                                }
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Terjadi Kesalahan',
                                    text: response.message
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error(xhr.responseText);
                            Swal.fire({
                                icon: 'error',
                                title: 'Terjadi Kesalahan',
                                text: 'Gagal memperbarui data. Silakan coba lagi.'
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
