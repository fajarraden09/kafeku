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
        // 1. Ambil semua menu dengan relasi resep & bahan baku (Eager Loading)
        $menus = Menu::with('resep.bahanBaku')->get();
        $kategori = Kategori::all();

        // 2. Loop melalui setiap menu untuk menghitung sisa porsi
        foreach ($menus as $menu) {
            // Jika menu tidak punya resep, anggap porsinya tidak terbatas
            if ($menu->resep->isEmpty()) {
                $menu->sisa_porsi = INF; // INF adalah konstanta PHP untuk Infinity
                continue; // Lanjut ke menu berikutnya
            }

            $porsiTersediaPerBahan = [];
            // 3. Loop melalui setiap bahan dalam resep menu ini
            foreach ($menu->resep as $resepItem) {
                $bahanBaku = $resepItem->bahanBaku;
                $jumlahDibutuhkan = $resepItem->jumlah_dibutuhkan;

                // Lewati jika bahan baku tidak ada atau jumlah dibutuhkan adalah 0
                if (!$bahanBaku || $jumlahDibutuhkan <= 0) {
                    continue;
                }

                // 4. Hitung: Berapa porsi yang bisa dibuat dari stok bahan ini?
                $porsiDariBahanIni = floor($bahanBaku->stok / $jumlahDibutuhkan);
                $porsiTersediaPerBahan[] = $porsiDariBahanIni;
            }

            // 5. Tentukan sisa porsi dari nilai terkecil (bahan yang paling sedikit)
            if (empty($porsiTersediaPerBahan)) {
                $menu->sisa_porsi = 0; // Jika tidak ada resep valid, porsi 0
            } else {
                $menu->sisa_porsi = min($porsiTersediaPerBahan);
            }
        }

        // 6. Kirim data menu yang sudah memiliki properti 'sisa_porsi' ke view
        return view('kasir.index', compact('menus', 'kategori'));
    }

    /**
     * Menyimpan pesanan baru (transaksi) dan melakukan sinkronisasi stok.
     */
    public function store(Request $request)
    {
        // Validasi input (tidak ada perubahan di sini)
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
                $affectedBahanBaku = [];

                // Loop untuk validasi awal dan hitung total harga (tidak ada perubahan di sini)
                foreach ($request->items as $item) {
                    $menu = Menu::with('resep.bahanBaku')->find($item['menu_id']);
                    if (!$menu) throw new \Exception('Menu tidak ditemukan.');
                    $totalHarga += $menu->harga * $item['jumlah'];
                    // Pengecekan stok awal bisa tetap di sini untuk keamanan
                }

                // 1. Buat record transaksi utama (tidak ada perubahan di sini)
                $transaksi = Transaksi::create([
                    'kode_transaksi'    => uniqid('TRX-'),
                    'user_id'           => Auth::id(),
                    'nama_pelanggan'    => $request->nama_pelanggan,
                    'total_harga'       => $totalHarga,
                    'metode_pembayaran' => $request->metode_pembayaran,
                    'status_pembayaran' => ($request->waktu_pembayaran === 'Langsung') ? 'Lunas' : 'Belum Dibayar',
                ]);

                // Loop untuk menyimpan detail dan mengurangi stok
                foreach ($request->items as $item) {
                    $menu = Menu::with('resep.bahanBaku')->find($item['menu_id']);

                    // ---- [MODIFIKASI 1] ----
                    // Simpan hasil pembuatan DetailTransaksi ke dalam variabel.
                    $detailTransaksi = DetailTransaksi::create([
                        'transaksi_id' => $transaksi->id,
                        'menu_id' => $menu->id,
                        'jumlah' => $item['jumlah'],
                        'harga_saat_transaksi' => $menu->harga,
                        'subtotal' => $menu->harga * $item['jumlah'],
                    ]);
                    // ------------------------

                    // Kurangi stok bahan baku
                    foreach($menu->resep as $resepItem) {
                        $bahanBaku = $resepItem->bahanBaku;
                        $totalPengurangan = $resepItem->jumlah_dibutuhkan * $item['jumlah'];

                        // ---- [MODIFIKASI 2] ----
                        // Panggil StockConsumptionService dengan ID detail transaksi sebagai parameter ketiga.
                        StockConsumptionService::consume($bahanBaku, $totalPengurangan, $detailTransaksi->id);
                        // ------------------------

                        $affectedBahanBaku[$bahanBaku->id] = $bahanBaku->fresh();
                    }
                }

                // Sinkronisasi service setelah stok dikurangi (tidak ada perubahan di sini)
                foreach ($affectedBahanBaku as $bahanBaku) {
                    StockNotificationService::checkAndNotify($bahanBaku);
                    foreach ($bahanBaku->resep()->with('menu')->get() as $resepTerkait) {
                        if($resepTerkait->menu) {
                            MenuAvailabilityService::update($resepTerkait->menu);
                        }
                    }
                }

                return $transaksi;
            });

            return redirect()->route('owner.kasir.index')->with('success', 'Transaksi berhasil! Kode: ' . $transaksi->kode_transaksi);

        } catch (Throwable $e) {
            Log::error("Transaksi Gagal: " . $e->getMessage());
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
