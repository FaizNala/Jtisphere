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
                <h5 class="alert-heading"><i class="icon fas fa-ban mr-2"></i>Data Tidak Ditemukan</h5>
                <p class="mb-0">Maaf, data yang Anda cari tidak dapat ditemukan dalam sistem kami.</p>
            </div>
            <a href="{{ url('/kegiatan') }}" class="btn btn-warning btn-block mt-3">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Halaman Kegiatan
            </a>
        </div>
    </div>
</div>
@else
<div id="modal-master" class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-0 shadow-lg">
        <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="exampleModalLabel">
                <i class="fas fa-info-circle mr-2"></i>Detail Data Kegiatan
            </h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body p-4">
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-header bg-light text-dark">
                            <h6 class="mb-0 font-weight-bold"><i class="fas fa-clipboard-list mr-2"></i>Informasi Kegiatan</h6>
                        </div>
                        <table class="table table-hover">
                            <tr>
                                <th class="w-25">Nama Kegiatan</th>
                                <td class="w-75"><strong>{{ $kegiatan->kegiatan_nama }}</strong></td>
                            </tr>
                            <tr>
                                <th class="text-dark">Kategori</th>
                                <td><span class="badge badge-info px-2 py-1">{{ $kegiatan->kategori_nama }}</span></td>
                            </tr>
                            <tr>
                                <th>Dokumen</th>
                                <td>
                                    @if($kegiatan->dokumen_nama)
                                        <a href="{{ asset('storage/surat_tugas/' . $kegiatan->dokumen_nama) }}" download class="btn btn-sm btn-primary">
                                            <i class="fas fa-download mr-2"></i>Download Surat Tugas
                                        </a>
                                    @else
                                        <span class="text-muted"><i class="fas fa-file-alt mr-2"></i>Tidak ada dokumen</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="card">
                        <div class="card-header bg-light text-dark">
                            <h6 class="mb-0 font-weight-bold"><i class="fas fa-users mr-2"></i>Dosen dan Peran</h6>
                        </div>
                        @if($dosenKegiatan->isEmpty())
                        <p class="text-muted">Tidak ada dosen yang terlibat dalam kegiatan ini.</p>
                        @else
                        <ul class="list-group">
                            @foreach($dosenKegiatan as $dk)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-user-tie text-primary mr-2"></i>{{ $dk->dosen->nama }}</span>
                                <span class="badge badge-primary px-2 py-1">{{ $dk->peran->peran_nama }}</span>
                            </li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                <i class="fas fa-times mr-2"></i>Tutup
            </button>
            <a href="" download="" class="btn btn-primary">
                <i class="fas fa-download mr-2"></i>Unduh Draft Surat Tugas
            </a>
        </div>
    </div>
</div>
@endempty
