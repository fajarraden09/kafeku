@extends('layout.main')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Laporan Menu Terlaris</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Daftar Menu Berdasarkan Jumlah Penjualan</h3>
                            </div>
                            <div class="card-body">
                                <table id="menuLarisTable" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Peringkat</th>
                                            <th>Nama Menu</th>
                                            <th class="text-center">Total Terjual</th>
                                            <th class="text-right">Total Pendapatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($menuTerlaris as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->menu->nama_menu ?? 'Menu Telah Dihapus' }}</td>
                                                <td class="text-center">{{ $item->total_terjual }} Porsi</td>
                                                <td class="text-right">Rp
                                                    {{ number_format($item->total_pendapatan, 0, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center">Belum ada data penjualan menu.</td>
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
        // Inisialisasi DataTable untuk fitur search, sort, dll.
        $(document).ready(function () {
            $('#menuLarisTable').DataTable({
                "ordering": false, // Matikan default sorting karena sudah diurutkan dari controller
                "language": {
                    "search": "Cari:",
                    "lengthMenu": "Tampilkan _MENU_ entri",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                    "paginate": {
                        "next": "Berikutnya",
                        "previous": "Sebelumnya"
                    },
                    "emptyTable": "Tidak ada data yang tersedia",
                    "zeroRecords": "Tidak ditemukan data yang cocok"
                }
            });
        });
    </script>
@endpush