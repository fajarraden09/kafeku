<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\BahanBaku;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Services\MenuAvailabilityService;
use App\Services\StockNotificationService;
use App\Services\StockConsumptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Tambahkan ini: Import facade Log
use App\Models\Kategori;
use Throwable;

class TransaksiController extends Controller
{
    /**
     * Menampilkan halaman kasir dengan menu yang tersedia.
     */
    public function index()
    {
        // Ambil semua menu yang status ketersediaannya true
        $menus = Menu::where('ketersediaan', true)->get();

        // Ambil semua kategori untuk tombol filter
        $kategori = Kategori::all();

        // Kirim KEDUA variabel ini ke view
        return view('kasir.index', compact('menus', 'kategori'));
    }

    /**
     * Menyimpan pesanan baru (transaksi) dan melakukan sinkronisasi stok.
     */
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|exists:menus,id',
            'items.*.jumlah' => 'required|integer|min:1',
            'nama_pelanggan' => 'required|string|max:255',
            'metode_pembayaran' => 'required|in:Tunai,QR-code',
            'waktu_pembayaran' => 'required|in:Langsung,Nanti',
        ]);

        try {
            $transaksi = DB::transaction(function () use ($request) {

                $totalHarga = 0;
                $affectedBahanBaku = []; // Inisialisasi array untuk bahan baku yang terpengaruh

                // Loop pertama untuk menghitung total harga dan melakukan pengecekan stok awal
                foreach ($request->items as $item) {
                    $menu = Menu::with('resep.bahanBaku')->find($item['menu_id']);

                    if (!$menu) {
                        throw new \Exception('Menu dengan ID ' . $item['menu_id'] . ' tidak ditemukan.');
                    }

                    $totalHarga += $menu->harga * $item['jumlah'];

                    // Pengecekan stok awal sebelum pengurangan
                    foreach($menu->resep as $resepItem) {
                        if (!$resepItem->bahanBaku) {
                            throw new \Exception('Bahan baku tidak ditemukan untuk resep menu ' . $menu->nama_menu);
                        }
                        $requiredQuantity = $resepItem->jumlah_dibutuhkan * $item['jumlah'];
                        if ($resepItem->bahanBaku->stok < $requiredQuantity) {
                            throw new \Exception('Stok ' . $resepItem->bahanBaku->nama_bahan . ' tidak mencukupi untuk menu ' . $menu->nama_menu . '. Dibutuhkan: ' . $requiredQuantity . ', Tersedia: ' . $resepItem->bahanBaku->stok);
                        }
                    }
                }

                // 1. Buat record transaksi utama
                $transaksi = Transaksi::create([
                    'kode_transaksi'    => uniqid('TRX-'),
                    'user_id'           => Auth::id(), // Pastikan user_id tersedia
                    'nama_pelanggan'    => $request->nama_pelanggan,
                    'total_harga'       => $totalHarga,
                    'metode_pembayaran' => $request->metode_pembayaran,
                    'status_pembayaran' => ($request->waktu_pembayaran === 'Langsung') ? 'Lunas' : 'Belum Dibayar',
                ]);

                // Loop kedua untuk menyimpan detail transaksi dan mengurangi stok dengan FEFO
                foreach ($request->items as $item) {
                    $menu = Menu::with('resep.bahanBaku')->find($item['menu_id']); // Muat ulang menu dengan relasi

                    // Simpan detail transaksi
                    DetailTransaksi::create([
                        'transaksi_id' => $transaksi->id,
                        'menu_id' => $menu->id,
                        'jumlah' => $item['jumlah'],
                        'harga_saat_transaksi' => $menu->harga,
                        'subtotal' => $menu->harga * $item['jumlah'],
                    ]);

                    // Kurangi stok bahan baku menggunakan StockConsumptionService (FEFO)
                    foreach($menu->resep as $resepItem) {
                        $bahanBaku = $resepItem->bahanBaku;
                        $totalPengurangan = $resepItem->jumlah_dibutuhkan * $item['jumlah'];

                        // Panggil StockConsumptionService untuk mengurangi stok dengan FEFO
                        StockConsumptionService::consume($bahanBaku, $totalPengurangan);

                        // Tambahkan bahan baku yang terpengaruh ke array untuk notifikasi dan update ketersediaan menu
                        // Gunakan fresh() untuk mendapatkan data bahanBaku terbaru setelah pengurangan stok
                        $affectedBahanBaku[$bahanBaku->id] = $bahanBaku->fresh();
                    }
                }

                // == SINKRONISASI SERVICE SETELAH SEMUA STOK DIKURANGI ==
                // Loop melalui bahan baku yang terpengaruh untuk update ketersediaan dan kirim notif
                foreach ($affectedBahanBaku as $bahanBaku) { // Variabel $bahanBaku sudah didefinisikan di sini
                    // Panggil service untuk memeriksa stok & kirim notifikasi jika perlu
                    StockNotificationService::checkAndNotify($bahanBaku); // $bahanBaku sudah fresh()

                    // Panggil service untuk update ketersediaan semua menu yang memakai bahan ini
                    // Pastikan relasi 'resep' di BahanBaku memuat 'menu'
                    foreach ($bahanBaku->resep()->with('menu')->get() as $resepTerkait) { // Perbaikan: $bahanBahanBaku menjadi $bahanBaku
                        if($resepTerkait->menu) {
                            MenuAvailabilityService::update($resepTerkait->menu);
                        }
                    }
                }

                return $transaksi;
            });

            return redirect()->route('owner.kasir.index')->with('success', 'Transaksi berhasil! Kode: ' . $transaksi->kode_transaksi);

        } catch (Throwable $e) { // Tangkap Throwable untuk semua jenis error
            // Log error untuk debugging
            Log::error("Transaksi Gagal: " . $e->getMessage()); // Menggunakan Log::error setelah di-import
            return redirect()->back()->with('error', 'Transaksi Gagal: ' . $e->getMessage());
        }
    }

    public function markAsPaid(Request $request, $id)
    {
        // Validasi bahwa metode pembayaran yang dipilih valid
        $request->validate([
            'metode_pembayaran' => 'required|in:Tunai,QR-code',
        ]);

        // Cari transaksi yang akan diupdate
        $transaksi = Transaksi::findOrFail($id);

        // Update status dan metode pembayarannya
        $transaksi->status_pembayaran = 'Lunas';
        $transaksi->metode_pembayaran = $request->metode_pembayaran;
        $transaksi->save();

        return redirect()->route('owner.laporan.index')->with('success', 'Transaksi ' . $transaksi->kode_transaksi . ' berhasil ditandai Lunas.');
    }
}
