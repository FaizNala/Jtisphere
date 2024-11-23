@extends('layouts.template')
@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Daftar Kegiatan</h3>
            <div class="card-tools">
                @if (auth()->user()->dosen->dosenLevel->first()->level->level_kode == 'ADM')
                    <button onclick="modalAction('{{ url('/kegiatan/import') }}')" class="btn btn-info">Import
                        Kegiatan</button>
                @endif
                <a href="{{ url('/kegiatan/export_excel') }}" class="btn btn-primary"><i class="fa fa-file-excel"></i> Export
                    Kegiatan</a>
                <a href="{{ url('/kegiatan/export_pdf') }}" class="btn btn-warning"><i class="fa fa-file-pdf"></i> Export
                    Kegiatan</a>
                @if (auth()->user()->dosen->dosenLevel->first()->level->level_kode == 'ADM')
                    <button onclick="modalAction('{{ url('/kegiatan/create_ajax') }}')" class="btn btn-success">Tambah Data
                        (Ajax)</button>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div id="filter" class="form-horizontal filter-date p-2 border-bottom mb-2">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-group-sm row text-sm mb-0">
                            <label for="filter_date" class="col-md-1 col-form-label">Filter</label>
                            <div class="col-md-3">
                                <select name="filter_kategori" class="form-control form-control-sm filter_kategori">
                                    <option value="">- Semua -</option>
                                    @foreach ($kategori as $l)
                                        <option value="{{ $l->kategori_id }}">{{ $l->kategori_nama }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Kategori Kagiatan</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
                        <th>Periode</th>
                        <th>Skala</th>
                        <th>Jumlah Dosen</th>
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
                        data: "kategori_nama",
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: "periode", // Kolom baru untuk jumlah dosen
                        orderable: true,
                        searchable: false
                    },
                    {
                        data: "skala", // Kolom baru untuk jumlah dosen
                        orderable: true,
                        searchable: false
                    },
                    {
                        data: "jumlah_dosen", // Kolom baru untuk jumlah dosen
                        orderable: true,
                        searchable: false
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
                    dataKegiatan.search(this.value).draw();
                }
            });

            $('.filter_kategori').change(function() {
                dataKegiatan.ajax.reload(); // Reload data ketika filter berubah
            });
        });
    </script>
@endpush
