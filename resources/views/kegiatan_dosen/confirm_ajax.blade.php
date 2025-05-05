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
    <form action="{{ url('/kegiatan_dosen/' . $kegiatan->kegiatan_id . '/delete_ajax') }}" method="POST" id="form-delete">
        @csrf
        @method('DELETE')
        <div id="modal-master" class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="exampleModalLabel">
                        <i class="fas fa-trash-alt mr-2"></i>Hapus Data Kegiatan
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-warning border-left border-warning" style="border-left-width: 5px;">
                        <h5 class="alert-heading mb-2"><i class="icon fas fa-exclamation-triangle mr-2"></i>Konfirmasi Penghapusan</h5>
                        <p class="mb-0">Apakah Anda yakin ingin menghapus data kegiatan berikut?</p>
                    </div>
                    <table class="table table-hover">
                        <tr>
                            <th class="text-right" style="width: 30%;">Nama Kegiatan:</th>
                            <td style="width: 70%;"><strong>{{ $kegiatan->kegiatan_nama }}</strong></td>
                        </tr>
                        <tr>
                            <th class="text-right">Kategori:</th>
                            <td><span class="badge badge-info px-2 py-1">{{ $kegiatan->kategori_nama }}</span></td>
                        </tr>
                        <tr>
                            <th class="text-right">Status:</th>
                            <td>{{ $kegiatan->status }}</td>
                        </tr>
                        <tr>
                            <th class="text-right">Tanggal Mulai:</th>
                            <td><i class="far fa-calendar-alt mr-2"></i>{{ $kegiatan->tanggal_mulai }}</td>
                        </tr>
                        <tr>
                            <th class="text-right">Tanggal Selesai:</th>
                            <td><i class="far fa-calendar-check mr-2"></i>{{ $kegiatan->tanggal_selesai }}</td>
                        </tr>
                        <tr>
                            <th class="text-right">Dosen dan Peran:</th>
                            <td>
                                @if($dosenKegiatan->isEmpty())
                                    <span>Tidak ada dosen yang terlibat.</span>
                                @else
                                    <ul class="list-group">
                                        @foreach($dosenKegiatan as $dk)
                                            <li class="list-item d-flex justify-content-between align-items-center">
                                                <span><i class="fas fa-user-tie text-primary mr-2"></i>{{ $dk->dosen->nama }}</span>
                                                <span class="badge badge-primary px-2 py-1">{{ $dk->peran->peran_nama }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </td>
                        </tr>
                        @if($kegiatan->dokumen_nama)
                        <tr>
                            <th class="text-right">Dokumen :</th>
                            <td>{{ $kegiatan->dokumen_nama }}</td>
                        </tr>
                        @endif
                    </table>
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
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        dataKegiatan.ajax.reload();
                                    }
                                });
                            } else {
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
                                text: 'Gagal menghapus data. Silakan coba lagi.'
                            });
                        }
                    });
                    return false;
                }
            });
        });
    </script>
@endempty
