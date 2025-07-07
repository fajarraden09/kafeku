<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\BahanBakuKeluar;
use App\Services\MenuAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\StockNotificationService;
use App\Services\StockConsumptionService; // Tambahkan ini: Import StockConsumptionService
use Throwable; // Tambahkan ini: Untuk menangkap semua jenis exception

class BahanKeluarController extends Controller
{
    /**
     * Menampilkan riwayat bahan baku yang keluar.
     */
    public function index()
    {
        // Ambil data, muat relasi bahanBaku dan user untuk ditampilkan
        $dataKeluar = BahanBakuKeluar::with('bahanBaku', 'user')->latest()->get();
        return view('Bahan_Keluar.keluar', compact('dataKeluar'));
    }

    /**
     * Menampilkan form untuk mencatat bahan keluar.
     */
    public function create()
    {
        // Ambil semua bahan baku untuk ditampilkan di dropdown form
        $bahan_baku = BahanBaku::orderBy('nama_bahan')->get();
        return view('Bahan_Keluar.create', compact('bahan_baku'));
    }

    /**
     * Menyimpan data bahan keluar & SINKRONISASI PENGURANGAN STOK (FEFO).
     */
     public function store(Request $request)
    {
        Log::info('--- STEP 1: Memulai proses Bahan Keluar ---');
        Log::info('Data Request:', $request->all());

        $request->validate([
            'bahan_baku_id' => 'required|exists:bahan_baku,id',
            'jumlah_keluar' => 'required|numeric|min:0.01',
            'keterangan'    => 'required|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $bahanBakuUtama = BahanBaku::find($request->bahan_baku_id); // Gunakan find() agar bisa dilempar exception jika null

                if (!$bahanBakuUtama) {
                    throw new \Exception("Bahan baku dengan ID " . $request->bahan_baku_id . " tidak ditemukan.");
                }

                Log::info('Bahan Baku ditemukan: ' . $bahanBakuUtama->nama_bahan . ' | Stok saat ini: ' . $bahanBakuUtama->stok);

                // Pengecekan stok awal sebelum pengurangan
                if ($bahanBakuUtama->stok < $request->jumlah_keluar) {
                    throw new \Exception('Stok ' . $bahanBakuUtama->nama_bahan . ' tidak mencukupi. Sisa stok hanya ' . $bahanBakuUtama->stok . ' ' . $bahanBakuUtama->satuan);
                }

                // Panggil StockConsumptionService untuk mengurangi stok dengan FEFO
                // Service ini akan mengurangi sisa_stok di batch_bahan_baku dan total stok di bahan_baku
                StockConsumptionService::consume($bahanBakuUtama, $request->jumlah_keluar);
                Log::info('Stok berhasil dikurangi via StockConsumptionService.');

                // Buat record bahan keluar
                BahanBakuKeluar::create([
                    'bahan_baku_id' => $request->bahan_baku_id,
                    'jumlah_keluar' => $request->jumlah_keluar,
                    'keterangan'    => $request->keterangan,
                    'user_id'       => Auth::id(),
                    // 'batch_id' tidak diisi di sini karena pengurangan bisa dari banyak batch
                ]);
                Log::info('Riwayat bahan keluar berhasil dicatat.');

                // Setelah stok dikurangi, perbarui notifikasi stok dan ketersediaan menu
                // Gunakan fresh() untuk mendapatkan data bahanBaku terbaru setelah pengurangan
                $bahanBakuFresh = $bahanBakuUtama->fresh();
                StockNotificationService::checkAndNotify($bahanBakuFresh);
                Log::info('Notifikasi stok diperiksa.');

                // Perbarui ketersediaan menu yang menggunakan bahan baku ini
                $resepTerkait = $bahanBakuFresh->resep()->with('menu')->get();
                Log::info('Menemukan ' . $resepTerkait->count() . ' resep yang terkait dengan bahan baku ini.');

                foreach ($resepTerkait as $resep) {
                    if ($resep->menu) {
                        Log::info('--- Memanggil MenuAvailabilityService untuk Menu: ' . $resep->menu->nama_menu . ' ---');
                        MenuAvailabilityService::update($resep->menu);
                    } else {
                        Log::warning('Resep ID ' . $resep->id . ' tidak memiliki relasi menu.');
                    }
                }
            });

            Log::info('--- PROSES SELESAI ---');
            // PERBAIKAN: Mengubah route dari 'owner.keluar.index' menjadi 'owner.bahan_keluar'
            return redirect()->route('owner.bahan_keluar')->with('success', 'Riwayat bahan keluar berhasil dicatat dan stok diperbarui.');

        } catch (Throwable $e) { // Tangkap Throwable untuk semua jenis error
            Log::error("Gagal mencatat bahan keluar: " . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal mencatat bahan keluar: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus riwayat bahan keluar (tidak mengembalikan stok).
     */
    public function delete($id)
    {
        // 1. Cari data riwayat bahan keluar berdasarkan ID
        $itemKeluar = BahanBakuKeluar::findOrFail($id);

        // 2. Langsung hapus data riwayat tersebut
        $itemKeluar->delete();

        // 3. Redirect kembali ke halaman index dengan pesan sukses
        return redirect()->route('owner.bahan_keluar')->with('success', 'Riwayat bahan keluar berhasil dihapus.');
    }
}
