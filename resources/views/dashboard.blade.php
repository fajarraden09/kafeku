@extends('layout.main')
@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Dashboard</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    {{-- Kotak Bahan Hampir Habis --}}
                    <div class="col-lg-4 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>{{ $lowStockItems->count() }}</h3>
                                <p>Bahan Hampir Habis</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-alert-circled"></i>
                            </div>
                            <a href="{{ route('owner.bahan_baku') }}" class="small-box-footer">Lihat Detail <i
                                    class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>

                    {{-- KOTAK BARU: Bahan Kadaluarsa --}}
                    <div class="col-lg-4 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>{{ $expiredItemsCount + $expiringSoonItemsCount }}</h3>
                                <p>Bahan Kadaluarsa / Hampir</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-ios-timer-outline"></i>
                            </div>
                            <a href="{{ route('owner.bahan_baku') }}" class="small-box-footer">Lihat Detail <i
                                    class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>

                    {{-- Kotak Menu Paling Laris --}}
                    <div class="col-lg-4 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                @if ($bestSellingMenu)
                                    <h3>{{ \Illuminate\Support\Str::limit($bestSellingMenu->menu->nama_menu, 15, '..') }}</h3>
                                    <p>Menu Laris (Terjual {{ $bestSellingMenu->total_terjual }})</p>
                                @else
                                    <h3>-</h3>
                                    <p>Menu Paling Laris</p>
                                @endif
                            </div>
                            <div class="icon">
                                <i class="ion ion-star"></i>
                            </div>
                            <a href="{{ route('owner.menu') }}" class="small-box-footer">Lihat Detail <i
                                    class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12">
                        <a href="{{ route('owner.dashboard', ['periode' => 'daily']) }}"
                            class="btn {{ $periode == 'daily' ? 'btn-primary' : 'btn-outline-primary' }}">Harian</a>
                        <a href="{{ route('owner.dashboard', ['periode' => 'weekly']) }}"
                            class="btn {{ $periode == 'weekly' ? 'btn-primary' : 'btn-outline-primary' }}">1 Minggu
                            Terakhir</a>
                        <a href="{{ route('owner.dashboard', ['periode' => 'monthly']) }}"
                            class="btn {{ $periode == 'monthly' ? 'btn-primary' : 'btn-outline-primary' }}">1 Bulan
                            Terakhir</a>
                        <a href="{{ route('owner.dashboard', ['periode' => 'yearly']) }}"
                            class="btn {{ $periode == 'yearly' ? 'btn-primary' : 'btn-outline-primary' }}">1 Tahun
                            Terakhir</a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12"> {{-- Diubah menjadi 12 kolom agar full width --}}
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-chart-line mr-1"></i>Grafik Penjualan</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="salesChart"
                                    style="min-height: 250px; height: 300px; max-height: 350px; width: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12"> {{-- Diubah menjadi 12 kolom agar full width --}}
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i>Arus Bahan Baku</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="stockFlowChart"
                                    style="min-height: 250px; height: 300px; max-height: 350px; width: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            'use strict'

            // Ambil data dari controller yang sudah di-format sebagai JSON
            var labels = @json($labels);
            var salesData = @json($dataPenjualan);
            var stockInData = @json($dataBahanMasuk);
            var stockOutData = @json($dataBahanKeluar);

            // --- Chart Penjualan ---
            var salesChartCanvas = $('#salesChart').get(0).getContext('2d')
            var salesChartData = {
                labels: labels,
                datasets: [
                    {
                        label: 'Pendapatan',
                        backgroundColor: 'rgba(60,141,188,0.9)',
                        borderColor: 'rgba(60,141,188,0.8)',
                        pointRadius: false,
                        pointColor: '#3b8bba',
                        pointStrokeColor: 'rgba(60,141,188,1)',
                        pointHighlightFill: '#fff',
                        pointHighlightStroke: 'rgba(60,141,188,1)',
                        data: salesData,
                        fill: false,
                    }
                ]
            }

            var salesChartOptions = {
                maintainAspectRatio: false,
                responsive: true,
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function (value, index, values) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }]
                }
            }

            new Chart(salesChartCanvas, {
                type: 'line',
                data: salesChartData,
                options: salesChartOptions
            })

            // --- Chart Arus Bahan Baku ---
            var stockFlowChartCanvas = $('#stockFlowChart').get(0).getContext('2d')
            var stockFlowChartData = {
                labels: labels,
                datasets: [
                    {
                        label: 'Bahan Masuk',
                        backgroundColor: '#28a745', // Hijau
                        borderColor: '#28a745',
                        data: stockInData
                    },
                    {
                        label: 'Bahan Keluar',
                        backgroundColor: '#dc3545', // Merah
                        borderColor: '#dc3545',
                        data: stockOutData
                    }
                ]
            }

            var stockFlowChartOptions = {
                maintainAspectRatio: false,
                responsive: true,
            }

            new Chart(stockFlowChartCanvas, {
                type: 'bar',
                data: stockFlowChartData,
                options: stockFlowChartOptions
            })
        })
    </script>

    {{-- SCRIPT UNTUK NOTIFIKASI POP-UP STOK RENDAH --}}
    <script>
        // Cek apakah ada pesan 'low_stock_alert' dari session
        @if (session('low_stock_alert'))
            // Jika ada, tampilkan notifikasi menggunakan SweetAlert2
            Swal.fire({
                title: 'Peringatan Stok Rendah!',
                text: "{{ session('low_stock_alert') }}",
                icon: 'warning',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Baik, saya mengerti'
            });
        @endif
    </script>
@endpush