@extends($layout . 'template')
@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Daftar Kegiatan</h3>
            <div class="card-tools">
                <a href="{{ url('/kegiatan/export_excel') }}" class="btn btn-primary"><i class="fa fa-file-excel"></i> Export Kegiatan</a>
                <a href="{{ url('/kegiatan/export_pdf') }}" class="btn btn-warning"><i class="fa fa-file-pdf"></i> Export Kegiatan</a>
                <button onclick="modalAction('{{ url('/kegiatan/create_ajax') }}')" class="btn btn-success">Tambah Data (Ajax)</button>
            </div>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif
            <table class="table table-bordered table-striped table-hover table-sm" id="table-kegiatan">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Kegiatan</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <div id="myModal" class="modal fade animate shake" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" data-width="75%" aria-hidden="true"></div>
@endsection

@push('css')
@endpush

@push('js')
    <script>
        function modalAction(url = '') {
            $('#myModal').load(url, function() {
                $('#myModal').modal('show');
            })
        }
        var dataKegiatan;
        $(document).ready(function() {
            dataKegiatan = $('#table-kegiatan').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    "url": "{{ url('kegiatan/list') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data": function(d) {
                        d.filter_kategori = $('#kategori_id').val(); // Filter by kategori_id
                        d.filter_status = $('#status').val(); // Filter by status
                    }
                },
                columns: [{
                        data: "DT_RowIndex",
                        className: "text-center",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: "kegiatan_nama",
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: "kategori_nama",
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: "status",
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: "aksi",
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $('#kategori_id, #status').on('change', function() {
                dataKegiatan.ajax.reload(); // Reload table when filter is applied
            });

            $('#table-kegiatan_filter input').unbind().bind().on('keyup', function(e) {
                if (e.keyCode == 13) {
                    dataKegiatan.search(this.value).draw();
                }
            });
        });
    </script>
@endpush
