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
                    <p class="mb-0">Maaf, data agenda yang Anda cari tidak dapat ditemukan dalam sistem kami.</p>
                </div>
                <a href="{{ url('/kegiatan_dosen') }}" class="btn btn-warning btn-block mt-3">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Halaman Agenda
                </a>
            </div>
        </div>
    </div>
@else
    <form action="{{ url('/kegiatan_dosen/' . $agenda->agenda_id . '/delete_agenda_ajax') }}" method="POST" id="form-delete">
        @csrf
        @method('DELETE')
        <div id="modal-master" class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="exampleModalLabel">
                        <i class="fas fa-trash-alt mr-2"></i>Hapus Data Agenda
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-warning border-left border-warning" style="border-left-width: 5px;">
                        <h5 class="alert-heading mb-2"><i class="icon fas fa-exclamation-triangle mr-2"></i>Konfirmasi Penghapusan</h5>
                        <p class="mb-0">Apakah Anda yakin ingin menghapus data agenda berikut?</p>
                    </div>
                    <table class="table table-hover">
                        <tr>
                            <th class="text-right" style="width: 30%;">Nama Agenda:</th>
                            <td style="width: 70%;"><strong>{{ $agenda->nama }}</strong></td>
                        </tr>
                        <tr>
                            <th class="text-right">Kegiatan:</th>
                            <td><span class="badge badge-info px-2 py-1">{{ $agenda->kegiatan_nama }}</span></td>
                        </tr>
                        <tr>
                            <th class="text-right">Status:</th>
                            <td>{{ $agenda->status }}</td>
                        </tr>
                        <tr>
                            <th class="text-right">Tanggal Mulai:</th>
                            <td><i class="far fa-calendar-alt mr-2"></i>{{ $agenda->tanggal_mulai }}</td>
                        </tr>
                        <tr>
                            <th class="text-right">Tanggal Selesai:</th>
                            <td><i class="far fa-calendar-check mr-2"></i>{{ $agenda->tanggal_selesai }}</td>
                        </tr>
                        <tr>
                            <th class="text-right">Dosen Terlibat:</th>
                            <td>
                                @if($agenda->dosen->isEmpty())
                                    <span>Tidak ada dosen yang terlibat.</span>
                                @else
                                    <ul class="list-group">
                                        @foreach($agenda->dosen as $dosen)
                                            <li class="list-item">
                                                <span><i class="fas fa-user-tie text-primary mr-2"></i>{{ $dosen->nama }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </td>
                        </tr>
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
                dataType: 'json', // Pastikan menggunakan dataType json
                success: function(response) {
                    if (response.status) {
                        $('#myModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message
                        }).then((result) => {
                            if (result.isConfirmed) {
                                dataAgenda.ajax.reload();
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Terjadi Kesalahan',
                            text: response.message,
                            footer: '<pre>' + JSON.stringify(response, null, 2) + '</pre>'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('XHR Response:', xhr.responseText);
                    console.error('Status:', status);
                    console.error('Error:', error);

                    let errorMessage = 'Gagal menghapus data';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        errorMessage = response.message || errorMessage;
                    } catch(e) {}

                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        text: errorMessage,
                        footer: '<pre>' + xhr.responseText + '</pre>'
                    });
                }
            });
            return false;
        }
    });
});
    </script>
@endempty
