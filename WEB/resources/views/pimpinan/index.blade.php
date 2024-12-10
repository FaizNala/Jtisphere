@extends('layouts.template')

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $totalKegiatan }}</h3>
                <p>Total Kegiatan</p>
            </div>
            <div class="icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $kegiatanBelum }}</h3>
                <p>Kegiatan Belum Berlangsung</p>
            </div>
            <div class="icon">
                <i class="fas fa-hourglass-start"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $kegiatanBerlangsung }}</h3>
                <p>Kegiatan Berlangsung</p>
            </div>
            <div class="icon">
                <i class="fas fa-play-circle"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $kegiatanSelesai }}</h3>
                <p>Kegiatan Selesai</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="card card-success">
    <div class="row">
        <!-- Pie Chart Kegiatan -->
        <div class="col-lg-6 col-12">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-1"></i>
                        Perbandingan Kegiatan Selesai dan Belum Terlaksana
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="remove">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="pieChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>

        <!-- Bar Chart Pengguna -->
        <div class="col-lg-6 col-12">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar mr-1"></i>
                        Data Pengguna
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="remove">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="userBarChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-chart-bar mr-1"></i>
            Beban Kerja Dosen
        </h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
            <button type="button" class="btn btn-tool" data-card-widget="remove">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <canvas id="barChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tabel Beban Kerja Semua Dosen</h3>

                <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 120px;">
                        <input type="text" name="table_search" class="form-control float-right" placeholder="Search">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Dosen</th>
                            <th>Jumlah Kegiatan</th>
                            <th>Status Kegiatan</th>
                            <th>Detail Beban Kerja Dosen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tbody>
                            @foreach($dosenKegiatan as $item)
                            <tr>
                                <td>{{ $item->dosen_id }}</td>
                                <td>{{ $item->dosen_nama }}</td>
                                <td>{{ $item->jumlah_kegiatan }}</td>
                                <td>
                                    <span data-toggle="tooltip"
                                          title="Belum: {{ $item->belum_terlaksana }}, Berjalan: {{ $item->berjalan }}, Selesai: {{ $item->selesai }}">
                                        Belum: {{ $item->belum_terlaksana }}, Selesai: {{ $item->selesai }}
                                    </span>
                                    <td>
                                        <button class="btn btn-info btn-sm" onclick="showDetail({{ $item->dosen_id }})">
                                            Detail
                                        </button>
                                    </td>
                                {{-- <td>{{ $item->nama_kegiatan }}</td> --}}
                            </tr>
                            @endforeach
                        </tbody>
                </table>
            </div>
        </div>
    </div>
<div id="myModal" class="modal fade animate shake" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" data-width="75%" aria-hidden="true"></div>
@endsection


@push('css')
<!-- ChartJS -->
<link rel="stylesheet" href="{{ asset('adminlte/plugins/chart.js/Chart.min.css') }}">
@endpush

@push('js')
<!-- jQuery -->
<script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
<!-- ChartJS -->
<script src="{{ asset('adminlte/plugins/chart.js/Chart.min.js') }}"></script>
<script>
$(function () {
    // Inisialisasi Bootstrap Tooltip
        $('[data-toggle="tooltip"]').tooltip();
    });
    function modalAction(url = '') {
            $('#myModal').load(url, function() {
                $('#myModal').modal('show');
            })
        }

    function showDetail(dosen_id) {
        $.ajax({
            url: '/statistik/' + dosen_id + '/show_ajax', // URL untuk permintaan AJAX
            type: 'GET',
            success: function(response) {
                // Tampilkan modal dengan data yang diterima
                $('#modal-master .modal-content').html(response);
                $('#modal-master').modal('show'); // Tampilkan modal
            },
            error: function(xhr) {
                alert('Data tidak ditemukan!'); // Tampilkan pesan error
            }
        });
    }

    // Data untuk pie chart (kegiatan selesai dan belum terlaksana)
    var pieData = {
        labels: [
            'Belum Terlaksana',
            'Selesai'
        ],
        datasets: [
            {
                data: [{{ $kegiatanBelum }}, {{ $kegiatanSelesai }}],
                backgroundColor: ['#007bff', '#28a745'], // Biru untuk belum terlaksana, hijau untuk selesai
            }
        ]
    }

    // Opsi pie chart
    var pieOptions = {
        maintainAspectRatio: false,
        responsive: true,
        plugins: {
            legend: {
                position: 'right'
            }
        }
    }

    // Membuat pie chart
    var pieChartCanvas = $('#pieChart').get(0).getContext('2d');
    new Chart(pieChartCanvas, {
        type: 'pie',
        data: pieData,
        options: pieOptions
    });

    // Data untuk bar chart pengguna
    var userBarData = {
        labels: ['Administrator', 'Pimpinan', 'Dosen'],
        datasets: [{
            label: 'Jumlah Pengguna',
            data: [{{ $admin }}, {{ $pimpinan }}, {{ $dosen }}],
            backgroundColor: ['#007bff', '#28a745', '#ffc107'],
            borderColor: ['#007bff', '#28a745', '#ffc107'],
            borderWidth: 1
        }]
    };


    // Opsi untuk bar chart pengguna
    var userBarOptions = {
        responsive: true,
        plugins: {
            legend: {
                display: false // Tidak menampilkan legend
            },
            tooltip: {
                callbacks: {
                    label: function(tooltipItem) {
                        return tooltipItem.raw + ' Pengguna'; // Menampilkan jumlah pengguna
                    }
                }
            }
        },
        scales: {
            x: {
                beginAtZero: true // Memulai sumbu X dari 0
            },
            y: {
                beginAtZero: true
            }
        }
    };

    // Membuat bar chart untuk data pengguna
    var userBarChartCanvas = $('#userBarChart').get(0).getContext('2d');
    new Chart(userBarChartCanvas, {
        type: 'bar',
        data: userBarData,
        options: userBarOptions
    });

    // Data untuk bar chart (Beban Kerja Dosen)
    var barData = {
        labels: [
            @foreach ($dosenKegiatan as $dosen)
                "{{ $dosen->dosen_nama }}", // Nama Dosen
            @endforeach
        ],
        datasets: [{
            label: 'Bobot Kerja',
            data: [
                @foreach ($dosenKegiatan as $dosen)
                    {{ $dosen->bobot_kerja }},
                @endforeach
            ],
            backgroundColor: [
                '#FF5733', '#33FF57', '#3357FF', '#F5A623', '#B93757', '#C799FF', '#F7DC6F', '#7F8C8D',
                '#FF8D1F', '#EC1C24' // Warna untuk masing-masing bar
            ],
            borderColor: [
                '#FF5733', '#33FF57', '#3357FF', '#F5A623', '#B93757', '#C799FF', '#F7DC6F', '#7F8C8D',
                '#FF8D1F', '#EC1C24' // Warna border
            ],
            borderWidth: 1
        }]
    };

    // Tambahkan total bobot kerja ke chart jika diperlukan
    console.log('Total Bobot Kerja:', {{ $totalBobotKerja }});

    // Opsi untuk bar chart horizontal dengan custom data labels
    var barOptions = {
        responsive: true,
        indexAxis: 'y', // Mengubah chart menjadi horizontal
        plugins: {
            legend: {
                display: false // Tidak menampilkan legend
            },
            tooltip: {
                callbacks: {
                    label: function(tooltipItem) {
                        return tooltipItem.raw + ' Bobot Kerja'; // Menampilkan jumlah bobot kerja
                    }
                }
            },
            datalabels: {
                color: '#fff', // Warna text label
                align: 'center',
                anchor: 'center',
                font: {
                    weight: 'bold',
                    size: 14
                },
                formatter: function(value, context) {
                    return value + ' Bobot'; // Menampilkan bobot kerja di label
                }
            }
        },
        scales: {
            x: {
                beginAtZero: true // Memulai sumbu X dari 0
            },
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 5      // Mengatur jarak antar angka
                },
                grid: {
                    display: false, // Menghilangkan garis grid
                    drawBorder: false // Menghilangkan garis batas sumbu Y
                }
            }
        }
    };

    // Membuat bar chart horizontal dengan data label
    var barChartCanvas = $('#barChart').get(0).getContext('2d');
    new Chart(barChartCanvas, {
        type: 'bar', // Tipe chart bar
        data: barData,
        options: barOptions
    });

</script>
@endpush
