<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\BatchBahanBaku;
use App\Services\MenuAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Services\StockNotificationService;
use Throwable; // Pastikan ini di-import untuk menangkap semua jenis error

class BahanMasukController extends Controller
{
    /**
     * Menampilkan riwayat bahan baku yang masuk.
     */
    public function index()
    {
        // Ambil semua data batch, muat relasi bahanBaku untuk ditampilkan namanya
        $batches = BatchBahanBaku::with('bahanBaku')->latest()->get();
        return view('Bahan_Masuk.masuk', compact('batches'));
    }

    /**
     * Menampilkan form untuk menambah data bahan masuk baru.
     */
    public function create()
    {
        // Ambil semua data bahan baku untuk ditampilkan di dropdown form
        $bahan_baku = BahanBaku::orderBy('nama_bahan')->get();
        return view('Bahan_Masuk.create', compact('bahan_baku'));
    }

    /**
     * Menyimpan data bahan masuk baru & SINKRONISASI STOK UTAMA.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bahan_baku_id'     => 'required|exists:bahan_baku,id',
            'jumlah_awal'       => 'required|numeric|min:0.01',
            'tanggal_kadaluarsa'=> 'nullable|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        // --- Perbaikan Struktur Try-Catch ---
        try {
            // Gunakan Transaksi Database untuk memastikan data konsisten
            DB::transaction(function () use ($request) {
                // 1. Buat record baru di tabel batch_bahan_baku
                $batch = BatchBahanBaku::create([
                    'bahan_baku_id'     => $request->bahan_baku_id,
                    'user_id'           => auth()->id(),
                    'jumlah_awal'       => $request->jumlah_awal,
                    'sisa_stok'         => $request->jumlah_awal, // Sisa stok awal = jumlah masuk
                    'tanggal_kadaluarsa'=> $request->tanggal_kadaluarsa,
                    // 'kode_batch' bisa digenerate otomatis jika perlu
                ]);

                // 2. SINKRONISASI: Tambah stok di tabel utama bahan_baku
                $bahanBakuUtama = BahanBaku::find($request->bahan_baku_id);
                if ($bahanBakuUtama) { // Tambahkan pengecekan ini untuk robustness
                    $bahanBakuUtama->increment('stok', $request->jumlah_awal);
                } else {
                    // Jika bahan baku tidak ditemukan, lempar exception agar transaksi di-rollback
                    throw new \Exception("Bahan Baku dengan ID " . $request->bahan_baku_id . " tidak ditemukan.");
                }

                // == SINKRONISASI SERVICE SETELAH STOK BERTAMBAH ==
                // 1. Reset flag notifikasi jika stok sudah kembali aman
                StockNotificationService::resetNotificationFlag($bahanBakuUtama->fresh());

                // 3. SINKRONISASI: Update ketersediaan menu yang menggunakan bahan ini
                // Ambil semua resep yang menggunakan bahan baku ini dan update menunya
                $resepTerkait = $bahanBakuUtama->resep()->with('menu')->get();
                foreach ($resepTerkait as $resep) {
                    if ($resep->menu) {
                        MenuAvailabilityService::update($resep->menu);
                    }
                }
            });

            // Redirect sukses di luar blok DB::transaction, tapi masih di dalam try
            return redirect()->route('owner.bahan_masuk')->with('success', 'Riwayat bahan masuk berhasil ditambahkan.');

        } catch (Throwable $e) { // Tangkap Throwable untuk semua jenis error
            // Ini akan menghentikan eksekusi dan menampilkan pesan error di browser
            // JANGAN LUPA HAPUS BARIS INI SETELAH MENDAPATKAN ERRORNYA DAN MASALAH TERATASI
            dd($e->getMessage());

            // Atau, jika Anda ingin logging tanpa menghentikan aplikasi:
            // \Log::error("Error saat menyimpan bahan masuk: " . $e->getMessage());
            // return redirect()->back()->withInput()->with('error', 'Gagal mencatat bahan masuk. Silakan coba lagi.');
        }
    } // Penutup method store()

    /**
     * Menghapus riwayat bahan masuk & SINKRONISASI STOK UTAMA.
     */
    public function delete($id)
    {
        // 1. Cari data batch yang akan dihapus berdasarkan ID
        $batch = BatchBahanBaku::findOrFail($id);

        // 2. Langsung hapus data batch dari database
        $batch->delete();

        // 3. Redirect kembali ke halaman index dengan pesan sukses
        return redirect()->route('owner.bahan_masuk')->with('success', 'Riwayat bahan masuk berhasil dihapus.');
    }
}