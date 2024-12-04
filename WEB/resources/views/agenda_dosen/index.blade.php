@extends('layouts.template')
@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Daftar Agenda Dosen</h3>
            <div class="card-tools">
                <a href="{{ url('/agenda_dosen/export_excel') }}" class="btn btn-primary"><i class="fa fa-file-excel"></i> Export
                    Agenda</a>
                <a href="{{ url('/agenda_dosen/export_pdf') }}" class="btn btn-warning"><i class="fa fa-file-pdf"></i> Export
                    Agenda</a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped table-hover table-sm" id="table-agenda">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Kegiatan</th>
                        <th>Agenda</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Selesai</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <div id="myModal" class="modal fade animate shake" tabindex="-1" role="dialog" data-backdrop="static"
        data-keyboard="false" data-width="75%" aria-hidden="true"></div>
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
        var dataAgenda;
        $(document).ready(function() {
            dataAgenda = $('#table-agenda').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    "url": "{{ url('agenda_dosen/list') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data": function(d) {
                        d.filter_kategori = $('.filter_kategori')
                    .val(); // Menggunakan class filter_kategori
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
                        data: "nama",
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

            $('#table-kegiatan_filter input').unbind().bind('keyup', function(e) {
                if (e.keyCode == 13) {
                    dataAgenda.search(this.value).draw();
                }
            });

            $('.filter_kategori').change(function() {
                dataAgenda.ajax.reload(); // Reload data ketika filter berubah
            });
        });
    </script>
@endpush
