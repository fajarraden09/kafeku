@extends('layout.main')

@section('content')
    {{-- CSS Khusus untuk Print --}}
    <style>
        @media print {

            /* Sembunyikan semua elemen yang tidak perlu saat mencetak */
            .main-sidebar,
            .main-header,
            .main-footer,
            .content-header,
            .btn,
            .dataTables_filter,
            .dataTables_info,
            .dataTables_paginate,
            .dataTables_length {
                display: none !important;
            }

            /* Pastikan konten laporan menggunakan seluruh halaman */
            .content-wrapper,
            .content,
            .container-fluid {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }

            .card {
                box-shadow: none !important;
                border: 1px solid #dee2e6 !important;
            }
        }
    </style>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Laporan Arus Bahan Baku</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row mb-3">
                    <div class="col-12">
                        <button id="btnPrint" class="btn btn-primary"><i class="fas fa-print"></i> Cetak Laporan</button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Riwayat Bahan Baku Masuk</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Nama Bahan</th>
                                            <th>Jumlah</th>
                                            <th>Oleh</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($bahanMasuk as $item)
                                            <tr>
                                                <td>{{ $item->created_at->format('d-m-Y') }}</td>
                                                <td>{{ $item->bahanBaku->nama_bahan ?? 'N/A' }}</td>
                                                <td>{{ $item->jumlah_awal }} {{ $item->bahanBaku->satuan ?? '' }}</td>
                                                <td>{{ $item->user->name ?? 'N/A' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center">Tidak ada data.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Riwayat Bahan Baku Keluar</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Nama Bahan</th>
                                            <th>Jumlah</th>
                                            <th>Keterangan</th>
                                            <th>Oleh</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($bahanKeluar as $item)
                                            <tr>
                                                <td>{{ $item->created_at->format('d-m-Y') }}</td>
                                                <td>{{ $item->bahanBaku->nama_bahan ?? 'N/A' }}</td>
                                                <td>{{ $item->jumlah_keluar }} {{ $item->bahanBaku->satuan ?? '' }}</td>
                                                <td>{{ $item->keterangan }}</td>
                                                <td>{{ $item->user->name ?? 'N/A' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">Tidak ada data.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Ringkasan Stok Bahan Baku Saat Ini</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Bahan</th>
                                            <th>Sisa Stok</th>
                                            <th>Satuan</th>
                                            <th>Status Stok</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($stokSaatIni as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->nama_bahan }}</td>
                                                <td>{{ $item->stok }}</td>
                                                <td>{{ $item->satuan }}</td>
                                                <td>
                                                    @if ($item->stok <= 0)
                                                        <span class="badge badge-danger">Habis</span>
                                                    @elseif ($item->stok <= $item->batas_minimum)
                                                        <span class="badge badge-warning">Hampir Habis</span>
                                                    @else
                                                        <span class="badge badge-success">Aman</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">Tidak ada data bahan baku.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
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
        // Script untuk tombol cetak
        document.getElementById('btnPrint').addEventListener('click', function () {
            window.print();
        });
    </script>
@endpush