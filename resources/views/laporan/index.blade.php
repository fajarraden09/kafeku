@extends('layout.main')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                {{-- ... header ... --}}
                <h1 class="m-0">Laporan Transaksi</h1>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <a href="{{ route('owner.laporan.index') }}" class="btn btn-secondary">Tampilkan Semua</a>
                            <a href="{{ route('owner.laporan.index', ['status' => 'Belum Dibayar']) }}"
                                class="btn btn-warning">
                                Tampilkan Hanya yang Belum Dibayar
                            </a>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Riwayat Transaksi Penjualan</h3>
                            </div>
                            <div class="card-body">
                                <table id="laporanTable" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th style="width: 10px;">No</th>
                                            {{-- <th>Kode Transaksi</th> --}}
                                            <th>Nama Pelanggan</th>
                                            <th>Tanggal</th>
                                            <th>Kasir</th>
                                            <th>Total Harga</th>
                                            <th>Status</th>
                                            <th class="text-center" style="width: 200px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($transaksi as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                {{-- <td>{{ $item->kode_transaksi }}</td> --}}
                                                <td>{{ $item->nama_pelanggan ?? 'N/A' }}</td>
                                                <td>{{ $item->created_at->format('d-m-Y H:i') }}</td>
                                                <td>{{ $item->user->name ?? 'N/A' }}</td>
                                                <td>Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                                                <td>
                                                    @if ($item->status_pembayaran == 'Lunas')
                                                        <span class="badge badge-success">Lunas</span>
                                                    @else
                                                        <span class="badge badge-warning">Belum Dibayar</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    {{-- AWAL DARI DROPDOWN AKSI --}}
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-secondary btn-sm dropdown-toggle"
                                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            Aksi
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            <a class="dropdown-item btn-detail" href="#"
                                                                data-url="{{ route('owner.laporan.show', ['id' => $item->id]) }}">
                                                                <i class="fas fa-eye text-info"></i> Detail
                                                            </a>

                                                            @if ($item->status_pembayaran == 'Belum Dibayar')
                                                                <a class="dropdown-item btn-payment" href="#"
                                                                    data-id="{{ $item->id }}">
                                                                    <i class="fas fa-check text-success"></i> Tandai Lunas
                                                                </a>
                                                            @endif

                                                            <div class="dropdown-divider"></div>

                                                            {{-- <a class="dropdown-item" href="#" data-toggle="modal"
                                                                data-target="#softDeleteModal{{ $item->id }}">
                                                                <i class="fas fa-eraser text-warning"></i> Hapus (Salah Input)
                                                            </a> --}}
                                                            <a class="dropdown-item" href="#" data-toggle="modal"
                                                                data-target="#forceDeleteModal{{ $item->id }}">
                                                                <i class="fas fa-trash-alt text-danger"></i> Hapus Permanen
                                                            </a>
                                                        </div>
                                                    </div>
                                                    {{-- AKHIR DARI DROPDOWN AKSI --}}
                                                </td>
                                            </tr>

                                            <div class="modal fade" id="paymentModal{{ $item->id }}">
                                                {{-- ... (Isi modal ini tidak berubah) ... --}}
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form
                                                            action="{{ route('owner.transaksi.markAsPaid', ['id' => $item->id]) }}"
                                                            method="POST">
                                                            @csrf
                                                            <div class="modal-header">
                                                                <h4 class="modal-title">Konfirmasi Pembayaran</h4> <button
                                                                    type="button" class="close"
                                                                    data-dismiss="modal">&times;</button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Konfirmasi pembayaran untuk
                                                                    <b>{{ $item->kode_transaksi }}</b>?
                                                                </p>
                                                                <div class="form-group">
                                                                    <label>Metode Pembayaran Akhir:</label>
                                                                    <select name="metode_pembayaran" class="form-control">
                                                                        <option value="Tunai">Tunai</option>
                                                                        <option value="QR-code">QR-code</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer justify-content-between"> <button
                                                                    type="button" class="btn btn-default"
                                                                    data-dismiss="modal">Batal</button> <button type="submit"
                                                                    class="btn btn-primary">Ya, Konfirmasi Lunas</button> </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal fade" id="softDeleteModal{{ $item->id }}">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form
                                                            action="{{ route('owner.laporan.softdelete', ['id' => $item->id]) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <div class="modal-header">
                                                                <h4 class="modal-title">Konfirmasi Hapus Riwayat</h4>
                                                                <button type="button" class="close"
                                                                    data-dismiss="modal">&times;</button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Hapus riwayat transaksi <b>{{ $item->kode_transaksi }}</b>
                                                                    karena kesalahan input?</p>
                                                                <p class="text-warning"><i
                                                                        class="fas fa-exclamation-triangle"></i> Riwayat ini
                                                                    akan disembunyikan dari laporan, namun tetap dihitung dalam
                                                                    total pendapatan di dashboard.</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-default"
                                                                    data-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-warning">Ya, Hapus dari
                                                                    Tampilan</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Modal untuk Hard Delete (Permanen) --}}
                                            <div class="modal fade" id="forceDeleteModal{{ $item->id }}">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form
                                                            action="{{ route('owner.laporan.forcedelete', ['id' => $item->id]) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <div class="modal-header bg-danger">
                                                                <h4 class="modal-title">Konfirmasi Hapus Permanen</h4>
                                                                <button type="button" class="close"
                                                                    data-dismiss="modal">&times;</button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Apakah Anda benar-benar yakin ingin menghapus transaksi
                                                                    <b>{{ $item->kode_transaksi }}</b> secara <b>PERMANEN</b>?
                                                                </p>
                                                                <p class="text-danger font-weight-bold"><i
                                                                        class="fas fa-skull-crossbones"></i> Aksi ini tidak
                                                                    dapat dibatalkan dan akan mengurangi total pendapatan di
                                                                    grafik dashboard Anda.</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-default"
                                                                    data-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-danger">Ya, Hapus
                                                                    Permanen</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">Belum ada data transaksi.</td>
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

    <div class="modal fade" id="detailModal">
        {{-- ... (Isi modal ini tidak berubah) ... --}}
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Transaksi</h5> <button type="button" class="close"
                        data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="modal-content-placeholder">
                        <p class="text-center">Memuat data...</p>
                    </div>
                </div>
                <div class="modal-footer"> <button type="button" class="btn btn-secondary"
                        data-dismiss="modal">Tutup</button> </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // 1. AKTIFKAN KEMBALI DATATABLES
            var table = $('#laporanTable').DataTable();

            // 2. EVENT LISTENER UNTUK TOMBOL DETAIL (tidak berubah, sudah benar)
            table.on('click', '.btn-detail', function () {
                var detailUrl = $(this).data('url');
                var modalContent = $('#modal-content-placeholder');

                $('#detailModal').modal('show');
                modalContent.html('<p class="text-center">Memuat data...</p>');

                $.ajax({
                    url: detailUrl,
                    type: 'GET',
                    success: function (response) {
                        var html = `
                                                    <p><strong>Kode Transaksi:</strong> ${response.kode_transaksi}</p>
                                                    <p><strong>Nama Pelanggan:</strong> ${response.nama_pelanggan ? response.nama_pelanggan : 'N/A'}</p>
                                                    <p><strong>Status:</strong> ${response.status_pembayaran}</p>
                                                    <p><strong>Metode Pembayaran:</strong> ${response.metode_pembayaran}</p>
                                                    <table class="table table-bordered mt-3">
                                                        <thead><tr><th>Menu</th><th>Jumlah</th><th>Harga</th><th>Subtotal</th></tr></thead>
                                                        <tbody>`;

                        response.detail_transaksi.forEach(function (item) {
                            html += `<tr><td>${item.menu ? item.menu.nama_menu : 'Menu Dihapus'}</td><td>${item.jumlah}</td><td>Rp ${Number(item.harga_saat_transaksi).toLocaleString('id-ID')}</td><td>Rp ${Number(item.subtotal).toLocaleString('id-ID')}</td></tr>`;
                        });

                        html += `</tbody><tfoot><tr><th colspan="3" class="text-right">Total:</th><th>Rp ${Number(response.total_harga).toLocaleString('id-ID')}</th></tr></tfoot></table>`;
                        modalContent.html(html);
                    },
                    error: function () {
                        modalContent.html('<p class="text-center text-danger">Gagal memuat data detail.</p>');
                    }
                });
            });

            // 3. TAMBAHKAN EVENT LISTENER BARU UNTUK TOMBOL "TANDAI LUNAS"
            table.on('click', '.btn-payment', function () {
                var transaksiId = $(this).data('id');
                var modalSelector = '#paymentModal' + transaksiId;

                // Perintahkan modal yang sesuai untuk muncul
                $(modalSelector).modal('show');
            });
        });
    </script>

@endpush