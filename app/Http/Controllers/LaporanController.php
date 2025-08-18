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
use App\Services\StockRestoreService;

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

   // app/Http/Controllers/LaporanController.php

    // app/Http/Controllers/LaporanController.php

    public function laporanStok(Request $request)
    {
        // Query dasar untuk bahan masuk dan keluar, diurutkan dari yang terbaru
        $queryMasuk = BatchBahanBaku::with('bahanBaku', 'user')->latest();
        $queryKeluar = BahanBakuKeluar::with('bahanBaku', 'user')->latest();
        
        // Inisialisasi variabel tanggal pencarian
        $tanggal_pencarian = null;

        // Kondisi 1: Jika ada input tanggal dari form pencarian
        if ($request->filled('tanggal')) {
            $tanggal_pencarian = Carbon::parse($request->tanggal)->format('Y-m-d');
            $queryMasuk->whereDate('created_at', $tanggal_pencarian);
            $queryKeluar->whereDate('created_at', $tanggal_pencarian);
        }
        // Kondisi 2: Jika ada filter dari tombol cepat
        elseif ($request->has('filter')) {
            $filter = $request->input('filter');
            $today = Carbon::today();

            switch ($filter) {
                case 'hari_ini':
                    $tanggal_pencarian = $today->format('Y-m-d');
                    $queryMasuk->whereDate('created_at', $tanggal_pencarian);
                    $queryKeluar->whereDate('created_at', $tanggal_pencarian);
                    break;
                
                // --- BAGIAN YANG DIPERBAIKI ---
                case 'minggu_ini':
                    // Logika baru: 7 Hari Terakhir
                    // Tanggal akhir adalah hari ini jam 23:59:59
                    $endDate = $today->copy()->endOfDay();
                    // Tanggal mulai adalah 6 hari sebelum hari ini jam 00:00:00
                    $startDate = $today->copy()->subDays(6)->startOfDay();
                    
                    $queryMasuk->whereBetween('created_at', [$startDate, $endDate]);
                    $queryKeluar->whereBetween('created_at', [$startDate, $endDate]);
                    break;
                // --- AKHIR BAGIAN YANG DIPERBAIKI ---

                case 'bulan_ini':
                    $startOfMonth = $today->copy()->startOfMonth();
                    $endOfMonth = $today->copy()->endOfMonth();
                    $queryMasuk->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
                    $queryKeluar->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
                    break;
            }
        }
        
        // Eksekusi query untuk mendapatkan hasilnya
        $bahanMasuk = $queryMasuk->get();
        $bahanKeluar = $queryKeluar->get();

        // Stok saat ini tetap tidak terpengaruh filter
        $stokSaatIni = BahanBaku::orderBy('nama_bahan', 'asc')->get();

        return view('laporan.stok', compact('bahanMasuk', 'bahanKeluar', 'stokSaatIni', 'tanggal_pencarian'));
    }

    public function cancel($id)
    {
        // Eager load hanya relasi yang dibutuhkan untuk proses pembatalan
        $transaksi = Transaksi::with('detailTransaksi')->find($id);

        if (!$transaksi) {
            return redirect()->back()->with('error', 'Transaksi tidak ditemukan.');
        }

        try {
            DB::transaction(function () use ($transaksi) {
                // 1. Panggil service untuk mengembalikan stok untuk setiap item pesanan.
                // Service akan membaca log konsumsi yang sudah tercatat.
                foreach ($transaksi->detailTransaksi as $detail) {
                    StockRestoreService::restore($detail);
                }

                // 2. Hapus data transaksi.
                // Logika di model Transaksi akan menghapus detailnya secara otomatis.
                $transaksi->delete();

                Log::info("Transaksi {$transaksi->kode_transaksi} berhasil dibatalkan dan stok dikembalikan.");
            });

            return redirect()->route('owner.laporan.index')->with('success', 'Transaksi berhasil dibatalkan dan stok telah dikembalikan dengan benar.');

        } catch (\Throwable $e) {
            Log::error("Gagal membatalkan transaksi ID {$id}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membatalkan transaksi. Terjadi kesalahan pada server.');
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