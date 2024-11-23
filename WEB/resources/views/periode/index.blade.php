@extends('layouts.template')
@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Daftar Periode</h3>
            <div class="card-tools">
                <a href="{{ url('/periode/export_excel') }}" class="btn btn-primary"><i class="fa fa-file-excel"></i> Export Periode</a>
                <a href="{{ url('/periode/export_pdf') }}" class="btn btn-warning"><i class="fa fa-file-pdf"></i> Export Periode</a>
                <button onclick="modalAction('{{ url('/periode/create_ajax') }}')" class="btn btn-success">Tambah Data (Ajax)</button>
            </div>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif
            <table class="table table-bordered table-striped table-hover table-sm" id="table-periode">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Periode</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Akhir</th>
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
        var dataPeriode;
        $(document).ready(function() {
            dataPeriode = $('#table-periode').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    "url": "{{ url('periode/list') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data": function(d) {
                        d.filter_kode = $('#periode').val(); // Filter by periode_kode
                    }
                },
                columns: [{
                        data: "DT_RowIndex",
                        className: "text-center",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: "periode",
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: "tanggal_mulai",
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: "tanggal_selesai",
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

            $('#periode').on('change', function() {
                dataPeriode.ajax.reload(); // Reload table when filter is applied
            });

            $('#table-periode_filter input').unbind().bind().on('keyup', function(e) {
                if (e.keyCode == 13) {
                    dataPeriode.search(this.value).draw();
                }
            });
        });
    </script>
@endpush
