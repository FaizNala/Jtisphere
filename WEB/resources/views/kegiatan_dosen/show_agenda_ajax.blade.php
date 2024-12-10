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
    <div id="modal-master" class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="exampleModalLabel">
                    <i class="fas fa-info-circle mr-2"></i>Detail Data Agenda
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
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
                                <span class="text-muted">Tidak ada dosen yang terlibat.</span>
                            @else
                                <ul class="list-unstyled">
                                    @foreach($agenda->dosen as $dosen)
                                        <li class="mb-2 d-flex align-items-center">
                                            <i class="fas fa-user-tie text-primary mr-2"></i>
                                            <span class="mr-3">{{ $dosen->nama }}</span>
                                            @if($dosen->dokumen_nama)
                                                <a href="{{ $dosen->dokumen_nama }}"
                                                   download class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-download mr-2"></i>Unduh
                                                </a>
                                            @endif
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
                    <i class="fas fa-times mr-2"></i>Tutup
                </button>
            </div>
        </div>
    </div>
@endempty
