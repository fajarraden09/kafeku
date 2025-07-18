<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi Harian - {{ $tanggal->format('d M Y') }}</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 20px;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h2,
        .header p {
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        tfoot th {
            text-align: right;
        }

        @media print {
            body {
                margin: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="header">
        <h2>SUGRIWA-SUBALI</h2>
        <p>Laporan Transaksi Harian</p>
        <p>Tanggal: <strong>{{ $tanggal->isoFormat('D MMMM YYYY') }}</strong></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Transaksi</th>
                <th>Nama Pelanggan</th>
                <th>Waktu</th>
                <th>Kasir</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transaksi as $trx)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $trx->kode_transaksi }}</td>
                    <td>{{ $trx->nama_pelanggan }}</td>
                    <td>{{ $trx->created_at->format('H:i') }}</td>
                    <td>{{ $trx->user->name ?? 'N/A' }}</td>
                    <td>Rp {{ number_format($trx->total_harga, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">Tidak ada transaksi pada tanggal ini.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5">Total Pendapatan</th>
                <th>Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <script>
        // Otomatis membuka dialog print saat halaman dimuat
        window.onload = function () {
            window.print();
        }
    </script>

</body>

</html>