@extends('layout.main')

@section('content')
    <div class="content-wrapper">
        {{-- ... (kode header Anda tidak berubah) ... --}}
        <div class="content-header">
            <div class="container-fluid">
                <h1 class="m-0">Laporan Transaksi</h1>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        {{-- ... (Tombol filter dan card header tidak berubah) ... --}}
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
                                    {{-- ... (Isi tabel thead dan tbody Anda tidak perlu diubah) ... --}}
                                    <thead>
                                        <tr>
                                            <th style="width: 10px;">No</th>
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
                                                            <a class="dropdown-item btn-print" href="#"
                                                                data-url="{{ route('owner.laporan.show', ['id' => $item->id]) }}">
                                                                <i class="fas fa-print text-primary"></i> Cetak Nota
                                                            </a>
                                                            <div class="dropdown-divider"></div>
                                                            <a class="dropdown-item" href="#" data-toggle="modal"
                                                                data-target="#cancelModal{{ $item->id }}">
                                                                <i class="fas fa-times-circle text-success"></i> Batalkan
                                                                Pesanan
                                                            </a>
                                                            <a class="dropdown-item" href="#" data-toggle="modal"
                                                                data-target="#forceDeleteModal{{ $item->id }}">
                                                                <i class="fas fa-trash-alt text-danger"></i> Hapus Permanen
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>

                                            {{-- ... (Semua modal Anda tidak perlu diubah) ... --}}
                                            <div class="modal fade" id="paymentModal{{ $item->id }}">
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
                                                                <div class="form-group"> <label>Metode Pembayaran Akhir:</label>
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
                                            <div class="modal fade" id="cancelModal{{ $item->id }}">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form action="{{ route('owner.laporan.cancel', ['id' => $item->id]) }}"
                                                            method="POST">
                                                            @csrf @method('DELETE')
                                                            <div class="modal-header bg-success">
                                                                <h4 class="modal-title">Konfirmasi Pembatalan</h4> <button
                                                                    type="button" class="close"
                                                                    data-dismiss="modal">&times;</button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Batalkan transaksi <b>{{ $item->kode_transaksi }}</b> karena
                                                                    salah input/dibatalkan pelanggan?</p>
                                                                <p class="text-success font-weight-bold"> <i
                                                                        class="fas fa-check-circle"></i> Aksi ini akan menghapus
                                                                    data transaksi dan mengembalikan stok bahan baku. </p>
                                                            </div>
                                                            <div class="modal-footer"> <button type="button"
                                                                    class="btn btn-default" data-dismiss="modal">Tutup</button>
                                                                <button type="submit" class="btn btn-success">Ya, Batalkan &
                                                                    Kembalikan Stok</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal fade" id="forceDeleteModal{{ $item->id }}">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form
                                                            action="{{ route('owner.laporan.forcedelete', ['id' => $item->id]) }}"
                                                            method="POST">
                                                            @csrf @method('DELETE')
                                                            <div class="modal-header bg-danger">
                                                                <h4 class="modal-title">Konfirmasi Hapus Permanen</h4> <button
                                                                    type="button" class="close"
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
                                                            <div class="modal-footer"> <button type="button"
                                                                    class="btn btn-default" data-dismiss="modal">Batal</button>
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

    {{-- ... (Modal detail tidak berubah) ... --}}
    <div class="modal fade" id="detailModal">
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

    <div id="printable-area" class="d-none"></div>
    <style>
        @media print {

            /* Sembunyikan semua elemen di halaman KECUALI area cetak */
            body * {
                visibility: hidden;
            }

            #printable-area,
            #printable-area * {
                visibility: visible;
            }

            /* Atur posisi area cetak ke sudut kiri atas */
            #printable-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 384px;
                /* Coba ini, atau sekitar 2.28 inci * 96dpi = ~220px, atau 2.28 * 203dpi = ~460px */
                display: block !important;
            }

            @page {
                size: 384px auto;
                /* Sesuaikan juga di sini */
                margin: 2mm;
            }

            html,
            body {
                margin: 0;
                padding: 0;
            }
        }
    </style>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Bagian DataTables dan Modal Detail tidak berubah
            var table = $('#laporanTable').DataTable();

            table.on('click', '.btn-detail', function () {
                var detailUrl = $(this).data('url');
                var modalContent = $('#modal-content-placeholder');
                $('#detailModal').modal('show');
                modalContent.html('<p class="text-center">Memuat data...</p>');
                $.ajax({
                    url: detailUrl,
                    type: 'GET',
                    success: function (response) {
                        var html = `<p><strong>Kode Transaksi:</strong> ${response.kode_transaksi}</p> <p><strong>Nama Pelanggan:</strong> ${response.nama_pelanggan ? response.nama_pelanggan : 'N/A'}</p> <p><strong>Status:</strong> ${response.status_pembayaran}</p> <p><strong>Metode Pembayaran:</strong> ${response.metode_pembayaran}</p> <table class="table table-bordered mt-3"> <thead><tr><th>Menu</th><th>Jumlah</th><th>Harga</th><th>Subtotal</th></tr></thead> <tbody>`;
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

            table.on('click', '.btn-payment', function () {
                var transaksiId = $(this).data('id');
                var modalSelector = '#paymentModal' + transaksiId;
                $(modalSelector).modal('show');
            });

            // [PERUBAHAN 3] Event listener untuk tombol Cetak Nota dengan HTML yang disesuaikan
            table.on('click', '.btn-print', function (e) {
                e.preventDefault();
                var detailUrl = $(this).data('url');

                $.ajax({
                    url: detailUrl,
                    type: 'GET',
                    success: function (response) {
                        var date = new Date(response.created_at);
                        var formattedDate = ('0' + date.getDate()).slice(-2) + '-' + ('0' + (date.getMonth() + 1)).slice(-2) + '-' + date.getFullYear() + ' ' + ('0' + date.getHours()).slice(-2) + ':' + ('0' + date.getMinutes()).slice(-2);

                        // HTML Nota (tidak ada perubahan di sini)
                        var receiptHtml = `
                                    <div style="font-family: 'sans-serif'; width: 100%; padding: 5px; font-size: 10pt; color: #000;">
                                        <div style="text-align: center;">
                                            <h3 style="margin: 0; font-size: 12pt; margin-top: 20px;">SUGRIWA-SUBALI</h3>
                                            <p style="margin: 0;">Jl. Tentara Pelajar, Wates, Kulon Progo</p>
                                            <p style="margin: 0;">Telp: 0896-1670-4229</p>
                                        </div>
                                        <hr style="border-top: 1px dashed #000; margin: 5px 0;">
                                        <table style="width: 100%; font-size: 10pt;">
                                            <tr><td style="width:30%;">No Struk</td><td>: ${response.kode_transaksi}</td></tr>
                                            <tr><td>Tanggal</td><td>: ${formattedDate}</td></tr>
                                            <tr><td>Kasir</td><td>: ${response.user ? response.user.name : 'N/A'}</td></tr>
                                            <tr><td>Pelanggan</td><td>: ${response.nama_pelanggan ? response.nama_pelanggan : '-'}</td></tr>
                                        </table>
                                        <hr style="border-top: 1px dashed #000; margin: 5px 0;">
                                        <table style="width: 100%; font-size: 10pt;">`;

                        response.detail_transaksi.forEach(function (item) {
                            var subtotal = item.jumlah * item.harga_saat_transaksi;
                            receiptHtml += `
                                        <tr><td colspan="3">${item.menu ? item.menu.nama_menu : 'Menu Dihapus'}</td></tr>
                                        <tr>
                                            <td style="text-align: right; padding-right: 10px;">${item.jumlah}x @${Number(item.harga_saat_transaksi).toLocaleString('id-ID')}</td>
                                            <td colspan="2" style="text-align: right;">${Number(subtotal).toLocaleString('id-ID')}</td>
                                        </tr>`;
                        });

                        receiptHtml += `
                                        </table>
                                        <hr style="border-top: 1px dashed #000; margin: 5px 0;">
                                        <table style="width: 100%; font-size: 10pt; font-weight: bold;">
                                            <tr><td>TOTAL</td><td style="text-align: right;">Rp ${Number(response.total_harga).toLocaleString('id-ID')}</td></tr>
                                            <tr><td>PEMBAYARAN</td><td style="text-align: right;">${response.metode_pembayaran}</td></tr>
                                        </table>
                                        <hr style="border-top: 1px dashed #000; margin: 5px 0;">
                                        <div style="text-align: center; margin-top: 10px">
                                            <p style="margin: 0";>Wifi : Sugriwa Subali</p>
                                            <p style="margin: 0";>Sandi: malamminggu</p>
                                            <p style="margin-top: 10px";>Terima Kasih Atas Kunjungan Anda</p>
                                        </div>
                                        <div style="text-align: center; ">

                                        </div>
                                    </div>
                                    `;

                        // Masukkan HTML ke area cetak
                        $('#printable-area').html(receiptHtml);

                        // [FIX 2] Beri jeda sebelum mencetak untuk memastikan konten sudah dirender
                        setTimeout(function () {
                            window.print();
                        }, 100); // Jeda 100 milidetik sudah cukup

                    },
                    error: function () {
                        alert('Gagal memuat data untuk dicetak.');
                    }
                });
            });
        });
    </script>
@endpush