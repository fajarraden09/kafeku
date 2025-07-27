@extends('layout.main')

@section('content')
    {{-- CSS Khusus untuk Print --}}
    <style>
        @media print {

            .main-sidebar,
            .main-header,
            .main-footer,
            .content-header,
            .btn,
            #filter-controls,
            .dataTables_filter,
            .dataTables_info,
            .dataTables_paginate {
                display: none !important;
            }

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

            h1.m-0,
            h3.card-title {
                text-align: center;
                width: 100%;
            }
        }
    </style>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <h1 class="m-0">Laporan Arus Bahan Baku</h1>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                {{-- Kumpulan Tombol Filter dan Pencarian --}}
                <div id="filter-controls" class="mb-3 d-flex align-items-center">
                    <a href="{{ route('owner.laporan.stok') }}" class="btn btn-secondary mr-2">Tampilkan Semua</a>
                    <a href="{{ route('owner.laporan.stok', ['filter' => 'hari_ini']) }}" class="btn btn-info mr-2">Hari
                        Ini</a>
                    <a href="{{ route('owner.laporan.stok', ['filter' => 'minggu_ini']) }}" class="btn btn-info mr-2">Minggu
                        Ini</a>
                    <a href="{{ route('owner.laporan.stok', ['filter' => 'bulan_ini']) }}" class="btn btn-info">Bulan
                        Ini</a>

                    <div class="ml-auto d-flex align-items-center">
                        <form action="{{ route('owner.laporan.stok') }}" method="GET" class="d-flex">
                            <input type="date" class="form-control" name="tanggal" value="{{ $tanggal_pencarian ?? '' }}">
                            <button class="btn btn-primary ml-2" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>

                        @if (isset($tanggal_pencarian))
                            <button id="btnPrint" class="btn btn-success ml-2">
                                <i class="fas fa-print"></i> Cetak Laporan
                            </button>
                        @else
                            {{-- Tombol Cetak Umum --}}
                            <button id="btnPrint" class="btn btn-primary ml-2"><i class="fas fa-print"></i> Cetak
                                Laporan</button>
                        @endif
                    </div>
                </div>

                {{-- ======================================================= --}}
                {{-- BAGIAN YANG DIUBAH UNTUK MEMBUAT TABEL BERDAMPINGAN --}}
                {{-- ======================================================= --}}
                <div class="row">
                    {{-- Kolom Kiri untuk Riwayat Masuk --}}
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
                                                <td>{{ $item->created_at->format('d-m-Y H:i') }}</td>
                                                <td>{{ $item->bahanBaku->nama_bahan ?? 'N/A' }}</td>
                                                <td>{{ $item->jumlah_awal }} {{ $item->bahanBaku->satuan ?? '' }}</td>
                                                <td>{{ $item->user->name ?? 'N/A' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center">Tidak ada data.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Kolom Kanan untuk Riwayat Keluar --}}
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
                                                <td>{{ $item->created_at->format('d-m-Y H:i') }}</td>
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
                {{-- ======================================================= --}}
                {{-- AKHIR DARI BAGIAN YANG DIUBAH --}}
                {{-- ======================================================= --}}

                {{-- Tabel Stok Saat Ini (Tetap di bawah dengan lebar penuh) --}}
                <div class="row">
                    <div class="col-12">
                        <div class="card mt-2">
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
        const btnPrint = document.getElementById('btnPrint');
        if (btnPrint) {
            btnPrint.addEventListener('click', function () {
                window.print();
            });
        }
    </script>
@endpush