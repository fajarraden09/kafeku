<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\BatchBahanBaku; // Import model BatchBahanBaku Anda
use App\Services\MenuAvailabilityService;
use Illuminate\Http\Request;
use Carbon\Carbon; // Import Carbon untuk manipulasi tanggal

class BahanBakuController extends Controller
{
    public function index()
    {
        // Ambil semua data bahan baku
        $data = BahanBaku::orderBy('stok', 'asc')->get();

        // Loop melalui setiap item bahan baku untuk menambahkan tanggal kadaluarsa terdekat
        foreach ($data as $item) {
            // Ambil tanggal kadaluarsa terdekat dari batch yang masih memiliki stok
            // Menggunakan relasi 'batches()' yang baru Anda tambahkan di model BahanBaku
            // Menggunakan 'sisa_stok' di tabel batch_bahan_baku
            $minDate = $item->batches()
                            ->where('sisa_stok', '>', 0) // Hanya batch yang masih ada sisa stoknya
                            ->whereNotNull('tanggal_kadaluarsa') // Hanya batch yang memiliki tanggal kadaluarsa
                            ->min('tanggal_kadaluarsa'); // Ambil tanggal kadaluarsa paling awal

            // Tambahkan properti baru ke objek bahan baku
            $item->tanggal_kadaluarsa_terdekat = $minDate ? Carbon::parse($minDate) : null;
        }

        return view('Data_Stok.stok', compact('data'));
    }

    public function create()
    {
        // Ganti path view jika berbeda, misal: 'owner.stok.create'
        return view('Data_Stok.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_bahan'    => 'required|string|max:255|unique:bahan_baku,nama_bahan',
            'stok'          => 'required|numeric|min:0',
            'satuan'        => 'required|string|max:50',
            'batas_minimum' => 'required|numeric|min:0',
        ]);

        BahanBaku::create($request->all());

        // Redirect ke route index yang sudah distandarisasi
        return redirect()->route('owner.bahan_baku')->with('success', 'Bahan baku berhasil ditambahkan.');
    }

    public function edit(string $id)
    {
        $data = BahanBaku::findOrFail($id);
         // Ganti path view jika berbeda, misal: 'owner.stok.edit'
        return view('Data_Stok.edit', compact('data'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'nama_bahan'    => 'required|string|max:255',
            'stok'          => 'required|integer|min:0',
            'satuan'        => 'required|string|max:50',
            'batas_minimum' => 'required|integer|min:0',
        ]);

        $bahanBaku = BahanBaku::findOrFail($id); // Perbaikan: seharusnya BahanBaku
        $bahanBaku->update($request->all());

        // <-- 2. MULAI SINKRONISASI SETELAH UPDATE STOK -->
        // Dapatkan semua resep yang menggunakan bahan baku ini
        $resepTerkait = $bahanBaku->resep()->with('menu')->get();

        // Loop melalui setiap resep dan update ketersediaan menu terkait
        foreach($resepTerkait as $resep) {
            if ($resep->menu) { // Pastikan relasi menu ada
                MenuAvailabilityService::update($resep->menu);
            }
        }
        // <-- SELESAI SINKRONISASI -->

        return redirect()->route('owner.bahan_baku')->with('success', 'Data bahan baku berhasil diperbarui.');
    }

    public function delete(string $id)
    {
        $bahanBaku = BahanBaku::findOrFail($id);

        // <-- 3. TAMBAHKAN PROTEKSI PENGHAPUSAN -->
        // Cek apakah bahan baku ini digunakan dalam resep
        if ($bahanBaku->resep()->exists()) {
            return redirect()->route('owner.bahan_baku')
                ->with('error', 'Gagal! Bahan baku "' . $bahanBaku->nama_bahan . '" masih digunakan dalam resep menu.');
        }

        $bahanBaku->delete();

        return redirect()->route('owner.bahan_baku')->with('success', 'Bahan baku berhasil dihapus.');
    }
}
