@extends($layout . 'template')

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Daftar Peran</h3>
            <div class="card-tools">
                <a href="{{ url('/peran/export_excel') }}" class="btn btn-primary"><i class="fa fa-file-excel"></i> Export Peran</a>
                <a href="{{ url('/peran/export_pdf') }}" class="btn btn-warning"><i class="fa fa-file-pdf"></i> Export Peran</a>
                <button onclick="modalAction('{{ url('/peran/create_ajax') }}')" class="btn btn-success">Tambah Data (Ajax)</button>
            </div>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif
            <table class="table table-bordered table-striped table-hover table-sm" id="table-peran">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kode Peran</th>
                        <th>Nama Peran</th>
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
        var dataPeran;
        $(document).ready(function() {
            dataPeran = $('#table-peran').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    "url": "{{ url('peran/list') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data": function(d) {
                        d.filter_kode = $('#peran_kode').val(); // Filter by peran_kode
                    }
                },
                columns: [{
                        data: "DT_RowIndex",
                        className: "text-center",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: "peran_kode",
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: "peran_nama",
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

            $('#peran_kode').on('change', function() {
                dataPeran.ajax.reload(); // Reload table when filter is applied
            });

            $('#table-peran_filter input').unbind().bind().on('keyup', function(e) {
                if (e.keyCode == 13) {
                    dataPeran.search(this.value).draw();
                }
            });
        });
    </script>
@endpush
