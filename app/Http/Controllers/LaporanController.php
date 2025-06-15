<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\BatchBahanBaku; 
use App\Models\BahanBakuKeluar; 
use App\Models\BahanBaku;  
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    /**
     * Menampilkan halaman utama laporan dengan daftar semua transaksi.
     */
    public function index(Request $request)
{
    $query = Transaksi::with('user')->latest();

    // Jika ada parameter status di URL, filter berdasarkan itu
    if ($request->has('status') && $request->status == 'Belum Dibayar') {
        $query->where('status_pembayaran', 'Belum Dibayar');
    }

    $transaksi = $query->get();

    return view('laporan.index', compact('transaksi'));
}

    /**
     * Mengambil detail sebuah transaksi untuk ditampilkan di modal.
     * Method ini akan merespon dengan data JSON.
     */
    public function show($id)
    {
        // Ambil data transaksi beserta detailnya, dan di dalam detail ambil juga data menunya.
        $transaksi = Transaksi::with('detailTransaksi.menu')->findOrFail($id);

        return response()->json($transaksi);
    }

    /**
     * Menghapus riwayat transaksi secara "soft delete".
     * Data hanya disembunyikan dan tetap terhitung di grafik.
     */
    public function softDelete($id)
    {
        $transaksi = Transaksi::findOrFail($id);
        $transaksi->delete(); // Ini akan menjalankan soft delete

        return redirect()->route('owner.laporan.index')->with('success', 'Riwayat transaksi (kesalahan input) berhasil dihapus dari tampilan.');
    }

    /**
     * Menghapus riwayat transaksi secara permanen dari database.
     */
    public function forceDelete($id)
    {
        // Gunakan withTrashed() untuk mencari data yang mungkin sudah di-soft-delete
        $transaksi = Transaksi::withTrashed()->findOrFail($id);
        $transaksi->forceDelete(); // Ini akan menjalankan hard delete

        return redirect()->route('owner.laporan.index')->with('success', 'Riwayat transaksi berhasil dihapus secara permanen.');
    }

    public function laporanStok()
    {
        // ... (query untuk bahanMasuk dan bahanKeluar tetap sama) ...
        $bahanMasuk = BatchBahanBaku::with('bahanBaku', 'user')->latest()->get();
        $bahanKeluar = BahanBakuKeluar::with('bahanBaku', 'user')->latest()->get();

        // Urutkan data berdasarkan kolom 'stok' dari yang terkecil ke terbesar
        $stokSaatIni = BahanBaku::orderBy('stok', 'asc')->get(); 

        return view('laporan.stok', compact('bahanMasuk', 'bahanKeluar', 'stokSaatIni'));
    }
}