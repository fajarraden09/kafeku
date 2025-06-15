<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class KasirController extends Controller
{
    public function dashboard()
    {
        return view('kasir.dashboard');
    }

    // Method untuk menampilkan halaman POS Kasir
    public function createTransaksi()
    {
        // Ambil semua data menu yang tersedia untuk ditampilkan
        $menus = Menu::where('ketersediaan', 'tersedia')->get();
        return view('kasir.transaksi-create', compact('menus'));
    }

    // Method untuk menyimpan data transaksi ke database
    public function storeTransaksi(Request $request)
    {
        // Validasi input
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|exists:menu,id',
            'items.*.jumlah' => 'required|integer|min:1',
            'total_harga' => 'required|numeric|min:0',
            'metode_pembayaran' => 'required|string',
        ]);

        // Menggunakan DB Transaction untuk memastikan integritas data
        DB::beginTransaction();
        try {
            // 1. Buat record baru di tabel 'transaksi'
            $transaksi = Transaksi::create([
                'kode_transaksi'    => 'INV-' . time(), // Contoh kode unik
                'user_id'           => Auth::id(), // ID kasir yang login
                'total_harga'       => $request->total_harga,
                'metode_pembayaran' => $request->metode_pembayaran,
                'tanggal_transaksi' => now(),
            ]);

            // 2. Loop melalui setiap item di keranjang dan simpan ke 'detail_transaksi'
            foreach ($request->items as $item) {
                $menu = Menu::find($item['menu_id']);

                DetailTransaksi::create([
                    'transaksi_id'  => $transaksi->id,
                    'menu_id'       => $item['menu_id'],
                    'jumlah'        => $item['jumlah'],
                    'subtotal'      => $menu->harga * $item['jumlah'],
                ]);

                // 3. (PENTING) Kurangi Stok Bahan Baku berdasarkan Resep
                // Loop melalui setiap resep untuk menu yang terjual
                foreach ($menu->resep as $resep) {
                    $bahanBaku = $resep->bahanBaku;
                    // Kurangi stok sebanyak (jumlah menu terjual * jumlah bahan yang dibutuhkan per menu)
                    $bahanBaku->stok_saat_ini -= ($item['jumlah'] * $resep->jumlah_dibutuhkan);
                    $bahanBaku->save();
                }
            }

            // Jika semua berhasil, commit transaksi
            DB::commit();

            return redirect()->route('kasir.dashboard')->with('success', 'Transaksi berhasil disimpan.');

        } catch (Exception $e) {
            // Jika ada error, rollback semua perubahan
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan transaksi: ' . $e->getMessage());
        }
    }
}