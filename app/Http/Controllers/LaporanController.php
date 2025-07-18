<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\BatchBahanBaku;
use App\Models\BahanBakuKeluar;
use App\Models\BahanBaku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon; 

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaksi::with('user')->latest();
        $tanggal_pencarian = null;
        $totalPendapatan = null; // Inisialisasi variabel total

        // Kondisi jika ada input tanggal dari form pencarian atau dari route laporan harian
        if ($request->has('tanggal') && $request->tanggal) {
            $tanggal_pencarian = Carbon::parse($request->tanggal)->format('Y-m-d');
            $query->whereDate('created_at', $tanggal_pencarian);

            // Langsung hitung total pendapatan untuk data yang akan ditampilkan
            // Kita kloning query agar tidak mengganggu pengambilan data utama
            $totalPendapatan = (clone $query)->sum('total_harga');
        }
        // Kondisi filter status "Belum Dibayar"
        elseif ($request->has('status') && $request->status == 'Belum Dibayar') {
            $query->where('status_pembayaran', 'Belum Dibayar');
        }

        $transaksi = $query->get();

        return view('laporan.index', compact('transaksi', 'tanggal_pencarian', 'totalPendapatan'));
    }

    /**
     * Mengambil detail sebuah transaksi untuk ditampilkan di modal atau dicetak.
     */
    public function show($id)
    {
        // Eager load relasi yang dibutuhkan untuk detail dan nota
        $transaksi = Transaksi::with('detailTransaksi.menu', 'user')->findOrFail($id);
        return response()->json($transaksi);
    }

    /**
     * Menampilkan laporan transaksi khusus untuk hari ini.
     */
    public function laporanHarian()
    {
        // Cukup arahkan ke route index dengan parameter tanggal hari ini
        return redirect()->route('owner.laporan.index', ['tanggal' => Carbon::today()->format('Y-m-d')]);
    }

    /**
     * Menghapus riwayat transaksi secara permanen dari database.
     */
    public function forceDelete($id)
    {
        $transaksi = Transaksi::withTrashed()->findOrFail($id);
        $transaksi->forceDelete();
        return redirect()->route('owner.laporan.index')->with('success', 'Riwayat transaksi berhasil dihapus secara permanen.');
    }

    public function laporanStok()
    {
        $bahanMasuk = BatchBahanBaku::with('bahanBaku', 'user')->latest()->get();
        $bahanKeluar = BahanBakuKeluar::with('bahanBaku', 'user')->latest()->get();
        $stokSaatIni = BahanBaku::orderBy('stok', 'asc')->get();
        return view('laporan.stok', compact('bahanMasuk', 'bahanKeluar', 'stokSaatIni'));
    }

    public function cancelAndRestock($id)
    {
        // Eager load semua relasi yang dibutuhkan dalam satu query
        $transaksi = Transaksi::with('detailTransaksi.menu.resep.bahanBaku')->find($id);

        if (!$transaksi) {
            return redirect()->route('owner.laporan.index')->with('error', 'Transaksi tidak ditemukan.');
        }

        DB::beginTransaction();
        try {
            foreach ($transaksi->detailTransaksi as $detail) {
                if ($detail->menu && $detail->menu->resep->isNotEmpty()) {
                    foreach ($detail->menu->resep as $resepItem) {
                        if ($resepItem->bahanBaku) {
                            $jumlahDipesan = $detail->jumlah;
                            
                            //❗️ PERBAIKAN DI SINI: Sesuaikan dengan nama kolom Anda
                            $stokPerResep = $resepItem->jumlah_dibutuhkan;
                            
                            $stokUntukDikembalikan = $jumlahDipesan * $stokPerResep;

                            $resepItem->bahanBaku->increment('stok', $stokUntukDikembalikan);
                        }
                    }
                }
            }

            // Hapus transaksi secara permanen
            $transaksi->forceDelete();

            DB::commit();
            return redirect()->route('owner.laporan.index')->with('success', 'Transaksi berhasil dibatalkan dan stok bahan baku telah dikembalikan.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal Batalkan Transaksi ID ' . $id . ': ' . $e->getMessage());
            return redirect()->route('owner.laporan.index')->with('error', 'Terjadi kesalahan saat membatalkan transaksi. Silakan coba lagi.');
        }
    }
    /**
     * Menyiapkan dan menampilkan halaman untuk mencetak laporan harian.
     */
    public function cetakLaporanHarian(Request $request)
    {
        // Validasi bahwa tanggal ada di request
        $request->validate(['tanggal' => 'required|date']);

        $tanggal = Carbon::parse($request->tanggal);
        
        // Ambil semua transaksi pada tanggal tersebut
        $transaksi = Transaksi::with('user')
            ->whereDate('created_at', $tanggal)
            ->latest()->get();

        // Hitung total pendapatan
        $totalPendapatan = $transaksi->sum('total_harga');

        // Kirim data ke view khusus untuk cetak
        return view('laporan.cetak_harian', compact('transaksi', 'totalPendapatan', 'tanggal'));
    }   
}