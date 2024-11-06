@extends('layouts.admin.template')

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
    <div class="card-header">
        <h3 class="card-title">Distribusi Pengguna</h3>
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

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Halo, apa kabar!</h3>
        <div class="card-tools"></div>
    </div>
    <div class="card-body">
        Selamat datang semua, ini adalah halaman utama dari aplikasi ini.
    </div>
</div>
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
    // Data untuk pie chart
    var pieData = {
        labels: [
            'Pimpinan',
            'Admin',
            'Dosen'
        ],
        datasets: [
            {
                data: [{{ $pimpinan }}, {{ $admin }}, {{ $dosen }}],
                backgroundColor: ['#007bff', '#28a745', '#ffc107'],
            }
        ]
    }

    // Opsi pie chart
    var pieOptions = {
        maintainAspectRatio: false,
        responsive: true,
        legend: {
            position: 'right'
        }
    }

    // Membuat pie chart
    var pieChartCanvas = $('#pieChart').get(0).getContext('2d')
    new Chart(pieChartCanvas, {
        type: 'pie',
        data: pieData,
        options: pieOptions
    })
})
</script>
@endpush
