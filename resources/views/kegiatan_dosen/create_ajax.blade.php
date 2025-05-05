<form action="{{ url('/kegiatan_dosen/ajax') }}" method="POST" id="form-tambah" enctype="multipart/form-data">
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
                    <input type="text" name="kegiatan_nama" id="kegiatan_nama" class="form-control"
                        placeholder="Masukkan nama kegiatan" required>
                    <small id="error-kegiatan_nama" class="error-text form-text text-danger"></small>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-calendar mr-2"></i>Periode</label>
                    <select name="periode_id" id="periode_id" class="form-control" required>
                        <option value="">Pilih Periode</option>
                        @foreach ($periode as $p)
                            <option value="{{ $p->periode_id }}">{{ $p->periode }}</option>
                        @endforeach
                    </select>
                    <small id="error-periode_id" class="error-text form-text text-danger"></small>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-globe mr-2"></i>Skala</label>
                    <select name="skala" id="skala" class="form-control" required>
                        <option value="">Pilih Skala</option>
                        <option value="Internal">Internal</option>
                        <option value="Nasional">Nasional</option>
                        <option value="Internasional">Internasional</option>
                        <option value="Lain-Lain">Lain-Lain</option>
                    </select>
                    <small id="error-skala" class="error-text form-text text-danger"></small>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-money-bill-wave mr-2"></i>Anggaran</label>
                    <input type="number" name="anggaran" id="anggaran" class="form-control" placeholder="Masukkan anggaran" required>
                    <small id="error-anggaran" class="error-text form-text text-danger"></small>
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
                    <label><i class="fas fa-align-left mr-2"></i>Deskripsi</label>
                    <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3" placeholder="Masukkan deskripsi kegiatan"
                        required></textarea>
                    <small id="error-deskripsi" class="error-text form-text text-danger"></small>
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
                    <label><i class="fas fa-file-upload mr-2"></i>Surat Tugas</label>
                    <input type="file" name="surat_tugas" id="surat_tugas" class="form-control-file">
                    <small id="error-surat_tugas" class="error-text form-text text-danger"></small>
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
                periode_id: {
                    required: true
                },
                skala: {
                    required: true
                },
                anggaran: {
                    required: true,
                    number: true
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
