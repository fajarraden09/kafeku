<!DOCTYPE html>
<html>

<body>
    <h1>Peringatan Stok Bahan Baku!</h1>
    <p>Halo Owner,</p>

    {{-- Tampilkan pesan dinamis yang sudah diformat dari StockNotificationService --}}
    <p>{{ $messageContent }}</p>

    {{-- Anda bisa tetap menyertakan detail tambahan jika diperlukan,
    tetapi pesan utama sudah ada di $messageContent --}}
    {{-- Contoh:
    <ul>
        <li><strong>Nama Bahan:</strong> {{ $bahanBaku->nama_bahan }}</li>
        <li><strong>Stok Total:</strong> {{ $bahanBaku->stok }} {{ $bahanBaku->satuan }}</li>
        <li><strong>Batas Minimum:</strong> {{ $bahanBaku->batas_minimum }} {{ $bahanBaku->satuan }}</li>
    </ul>
    --}}

    <p>Mohon untuk segera melakukan tindakan yang diperlukan.</p>
    <p>Terima kasih.</p>
</body>

</html>