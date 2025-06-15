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
     * Menyimpan data bahan keluar & SINKRONISASI PENGURANGAN STOK.
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

        $bahanBakuUtama = BahanBaku::findOrFail($request->bahan_baku_id);
        Log::info('Bahan Baku ditemukan: ' . $bahanBakuUtama->nama_bahan . ' | Stok saat ini: ' . $bahanBakuUtama->stok);
        
        if ($bahanBakuUtama->stok < $request->jumlah_keluar) {
            Log::error('Validasi Gagal: Stok tidak mencukupi.');
            return redirect()->back()
                ->withInput()
                ->withErrors(['jumlah_keluar' => 'Stok tidak mencukupi. Sisa stok ' . $bahanBakuUtama->nama_bahan . ' hanya ' . $bahanBakuUtama->stok . ' ' . $bahanBakuUtama->satuan]);
        }

        DB::transaction(function () use ($request, $bahanBakuUtama) {
            Log::info('--- STEP 2: Masuk ke dalam Transaksi Database ---');
            
            BahanBakuKeluar::create([
                'bahan_baku_id' => $request->bahan_baku_id,
                'jumlah_keluar' => $request->jumlah_keluar,
                'keterangan'    => $request->keterangan,
                'user_id'       => Auth::id(),
            ]);
            Log::info('Riwayat bahan keluar berhasil dicatat.');

            $bahanBakuUtama->decrement('stok', $request->jumlah_keluar);
            Log::info('Stok utama berhasil dikurangi. Stok baru: ' . $bahanBakuUtama->fresh()->stok);

             // == SINKRONISASI SERVICE SETELAH STOK BERKURANG ==
            // 1. Periksa dan kirim notifikasi jika stok mencapai batas minimum
            StockNotificationService::checkAndNotify($bahanBakuUtama->fresh());

            $resepTerkait = $bahanBakuUtama->resep()->with('menu')->get();
            Log::info('Menemukan ' . $resepTerkait->count() . ' resep yang terkait dengan bahan baku ini.');

            foreach ($resepTerkait as $resep) {
                if ($resep->menu) {
                    Log::info('--- STEP 3: Memanggil MenuAvailabilityService untuk Menu: ' . $resep->menu->nama_menu . ' ---');
                    MenuAvailabilityService::update($resep->menu);
                } else {
                    Log::warning('Resep ID ' . $resep->id . ' tidak memiliki relasi menu.');
                }
            }
        });

        Log::info('--- PROSES SELESAI ---');
        return redirect()->route('owner.bahan_keluar')->with('success', 'Riwayat bahan keluar berhasil dicatat.');
    }

       /**
     * Menghapus riwayat bahan keluar & SINKRONISASI PENGEMBALIAN STOK.
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