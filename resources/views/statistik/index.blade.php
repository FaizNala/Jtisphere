@extends('layouts.template')

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Daftar User</h3>
            <div class="card-tools">
                <a href="{{ url('/statistik/export_excel') }}" class="btn btn-primary"><i class="fa fa-file-excel"></i> Export
                    Statistik</a>
                <a href="{{ url('/statistik/export_pdf') }}" class="btn btn-warning"><i class="fa fa-file-pdf"></i> Export
                    Statistik</a>
            </div>
        </div>
        <div class="card-body">
            <!-- untuk Filter data -->
            <div id="filter" class="form-horizontal filter-date p-2 border-bottom mb-2">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-group-sm row text-sm mb-0">
                            <label for="filter_date" class="col-md-1 col-form-label">Filter</label>
                            <div class="col-md-3">
                                <select name="filter_level" class="form-control form-control-sm filter_level">
                                    <option value="">- Semua -</option>
                                    @foreach ($level as $l)
                                        <option value="{{ $l->level_id }}">{{ $l->level_nama }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Level User</small>
                            </div>
                            <!-- Di dalam div filter -->
                            <div class="col-md-3">
                                <select name="filter_periode" id="filter_periode" class="form-control form-control-sm">
                                    {{-- <option value="">- Semua -</option> --}}
                                    @foreach ($periode as $p)
                                        <option value="{{ $p->periode_id }}">{{ $p->periode }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Pilih Periode</small>
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
            <div style="overflow-x: auto;">
                <table class="table table-bordered table-striped table-hover table-sm" id="table-user">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Nama</th>
                            <th>Level Pengguna</th>
                            <th>Total Kegiatan</th>
                            <th>Beban Kerja</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
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
                // Inisialisasi Select2 setelah modal dimuat
                if ($('.select2-multiple').length) {
                    $('.select2-multiple').select2({
                        placeholder: "Pilih Level Pengguna",
                        allowClear: true,
                        dropdownParent: $('#myModal') // Penting untuk Select2 dalam modal
                    });
                }
            })
        }

        var dataUser;
        $(document).ready(function() {
            dataUser = $('#table-user').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    "url": "{{ url('statistik/list') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data": function(d) {
                        d.filter_level = $('.filter_level').val();
                        d.filter_periode = $('#filter_periode').val();
                        d._token = "{{ csrf_token() }}";
                    }
                },
                columns: [{
                        data: "DT_RowIndex",
                        className: "text-center",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: "username",
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: "nama",
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: "level_nama",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'total_kegiatan',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: "total_bobot",
                        orderable: false,
                        searchable: false
                    },

                    {
                        data: "aksi",
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $('#table-user_filter input').unbind().bind().on('keyup', function(e) {
                if (e.keyCode == 13) {
                    dataUser.search(this.value).draw();
                }
            });

            $('.filter_level').change(function() {
                dataUser.draw();
            });

            // Pada script jQuery
            $('#filter_periode').change(function() {
                dataUser.draw(); // Gunakan draw() untuk memuat ulang tabel
            });
        });

        // Tambahkan handler untuk membersihkan Select2 saat modal ditutup
        $('#myModal').on('hidden.bs.modal', function() {
            if ($('.select2-multiple').length) {
                $('.select2-multiple').select2('destroy');
            }
        });
    </script>
@endpush
