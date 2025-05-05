@empty($kegiatan)
<!-- Kode untuk pesan error tetap sama -->
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
                                <th>Tanggal Mulai</th>
                                <td><i class="far fa-calendar-alt mr-2"></i>{{ $kegiatan->tanggal_mulai }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal Selesai</th>
                                <td><i class="far fa-calendar-check mr-2"></i>{{ $kegiatan->tanggal_selesai }}</td>
                            </tr>
                            <tr>
                                <th>Dokumen</th>
                                <td>
                                    @if($kegiatan->dokumen_nama)
                                        <div class="d-flex align-items-center">
                                            <a href="{{ $kegiatan->dokumen_nama }}" download class="btn btn-sm btn-primary mr-2">
                                                <i class="fas fa-download mr-2"></i>Download Surat Tugas
                                            </a>
                                            {{-- <span class="text-success"><i class="fas fa-file-alt mr-1"></i>Dokumen tersedia</span> --}}
                                        </div>
                                    @else
                                        <span class="text-muted"><i class="fas fa-file-alt mr-2"></i>Tidak ada dokumen</span>
                                        <div class="mt-2">
                                            <a href="{{ url('kegiatan/' . $kegiatan->kegiatan_id . '/export_draft_surat_tugas') }}" class="text-primary" style="cursor: pointer;">
                                                <i class="fas fa-download mr-1"></i>Unduh Draft Surat Tugas
                                            </a>
                                        </div>
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
        </div>
    </div>
</div>
@endempty
