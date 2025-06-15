<!DOCTYPE html>
<html>

<body>
    <h1>Peringatan Stok Bahan Baku Hampir Habis!</h1>
    <p>Halo Owner,</p>
    <p>Stok untuk bahan baku di bawah ini telah mencapai batas minimum:</p>
    <ul>
        <li><strong>Nama Bahan:</strong> {{ $bahanBaku->nama_bahan }}</li>
        <li><strong>Sisa Stok:</strong> {{ $bahanBaku->stok }} {{ $bahanBaku->satuan }}</li>
        <li><strong>Batas Minimum:</strong> {{ $bahanBaku->batas_minimum }} {{ $bahanBaku->satuan }}</li>
    </ul>
    <p>Mohon untuk segera melakukan pemesanan ulang.</p>
</body>

</html>