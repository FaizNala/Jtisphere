@empty($kegiatan)
    <!-- Kode untuk pesan error tetap sama -->
@else
    <div id="modal-master" class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="exampleModalLabel">
                    <i class="fas fa-plus-circle mr-2"></i>Data Agenda Kegiatan
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-4">
                    <div class="d-flex justify-content-end mb-2">
                        <button type="button" class="btn btn-sm btn-success"
                            onclick="modalAction('{{ url('/kegiatan_dosen/' . $kegiatan->kegiatan_id . '/create_agenda_ajax') }}')">
                            <i class="fas fa-plus mr-2"></i>Tambah Agenda
                        </button>
                    </div>
                    <table class="table table-striped table-hover table-bordered">
                        <thead class="bg-info text-white">
                            <tr>
                                <th>No</th>
                                <th>Agenda</th>
                                <th>Jumlah Dosen</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($agenda as $item)
                                <tr>
                                    <td>{{ $loop->index + 1 }}</td>
                                    <td>{{ $item->nama }}</td>
                                    <td>{{ $item->jumlah_dosen }}</td>
                                    <td>{{ $item->status}}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-info"
                                                onclick="modalAction('{{ url('/kegiatan_dosen/' . $item->agenda_id . '/show_agenda_ajax') }}')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-warning"
                                                onclick="modalAction('{{ url('/kegiatan_dosen/' . $item->agenda_id . '/edit_agenda_ajax') }}')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                onclick="modalAction('{{ url('/kegiatan_dosen/' . $item->agenda_id . '/delete_agenda_ajax') }}')">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada kegiatan yang terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
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
