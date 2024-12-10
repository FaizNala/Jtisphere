@extends('layouts.template')

@section('content')
<div class="row">
    <!-- Total Kegiatan -->
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-calendar-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Kegiatan</span>
                <span class="info-box-number">{{ $totalKegiatanDosen }}</span>
            </div>
        </div>
    </div>

    <!-- Kegiatan Belum Berlangsung -->
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-hourglass-start"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Kegiatan Belum Berlangsung</span>
                <span class="info-box-number">{{ $kegiatanBelumDosen }}</span>
            </div>
        </div>
    </div>

    <!-- Kegiatan Berlangsung -->
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-play-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Kegiatan Berlangsung</span>
                <span class="info-box-number">{{ $kegiatanBerlangsungDosen }}</span>
            </div>
        </div>
    </div>

    <!-- Kegiatan Selesai -->
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box">
            <span class="info-box-icon bg-danger"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Kegiatan Selesai</span>
                <span class="info-box-number">{{ $kegiatanSelesaiDosen }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Pie Chart -->
    <div class="col-md-6">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie mr-1"></i>
                    Perbandingan Kegiatan Selesai dan Belum Terlaksana
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="pieChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>

    <!-- Progress Table -->
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Progress Kegiatan Anda Jangan Lupa Diupdate!</h3>
            </div>
            <div class="card-body table-responsive p-0" style="height: 300px; overflow-y: auto;">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kegiatan</th>
                            <th>Progress</th>
                            <th>Label</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kegiatanDosen as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->kegiatan_nama }}</td>
                            <td>
                                <div class="progress progress-sm">
                                    @php
                                    $progress = $item->status == 'Belum' ? 0 : ($item->status == 'Berjalan' ? 50 : 100);
                                    $progressBarColor = $item->status == 'Belum' ? 'bg-info' : ($item->status == 'Berjalan' ? 'bg-warning' : 'bg-success');
                                    @endphp
                                    <div class="progress-bar {{ $progressBarColor }}" style="width: {{ $progress }}%"></div>
                                </div>
                            </td>
                            <td><span class="badge {{ $progressBarColor }}">{{ $progress }}%</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Daftar Kegiatan -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Kegiatan Anda</h3>
                <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 120px;">
                        <input type="text" name="table_search" class="form-control float-right" placeholder="Search">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kegiatan</th>
                            <th>Status</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th>Skala</th>
                            <th>Detail Kegiatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kegiatanDosen as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->kegiatan_nama }}</td>
                            <td>{{ $item->status }}</td>
                            <td>{{ $item->tanggal_mulai }}</td>
                            <td>{{ $item->tanggal_selesai }}</td>
                            <td>{{ $item->skala }}</td>
                            <td>
                                <button onclick="showDetail({{ $item->kegiatan_id }})" class="btn btn-info btn-sm">Detail</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('adminlte/plugins/chart.js/Chart.min.css') }}">
@endpush

@push('js')
<script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('adminlte/plugins/chart.js/Chart.min.js') }}"></script>
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });

    function showDetail(kegiatan_id) {
        $.ajax({
            url: '/kegiatan/' + kegiatan_id + '/show_ajax',
            type: 'GET',
            success: function (response) {
                $('#modal-master .modal-content').html(response);
                $('#modal-master').modal('show');
            },
            error: function () {
                alert('Data tidak ditemukan!');
            }
        });
    }

    var pieData = {
        labels: ['Belum Terlaksana', 'Selesai'],
        datasets: [{
            data: [{{ $kegiatanBelumDosen }}, {{ $kegiatanSelesaiDosen }}],
            backgroundColor: ['#007bff', '#28a745'],
        }]
    };

    var pieOptions = {
        maintainAspectRatio: false,
        responsive: true,
        plugins: {
            legend: {
                position: 'right',
            },
        },
    };

    var pieChartCanvas = $('#pieChart').get(0).getContext('2d');
    new Chart(pieChartCanvas, {
        type: 'pie',
        data: pieData,
        options: pieOptions,
    });
</script>
@endpush
