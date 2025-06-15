<?php

namespace App\Http\Controllers;

use App\Models\Menu; // <-- PASTIKAN ANDA MENGIMPOR MODEL MENU
use Illuminate\Http\Request;
use App\Models\BahanBaku; 
use Illuminate\Support\Facades\Validator;
use App\Models\ResepMenu; 
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Storage; 
use App\Models\Kategori; 
use App\Services\MenuAvailabilityService;

class MenuController extends Controller
{
    public function index()
    {
        $data = Menu::with('kategori')->latest()->get();

        return view('Data_Menu.menu', compact('data'));
    }
    

    /**
     * Menyimpan data menu baru ke database.
     */

    public function create()
    {
        $bahan_baku = BahanBaku::orderBy('nama_bahan')->get();
        $kategori = Kategori::orderBy('nama_kategori')->get();

        // Kirim variabel $kategori ke view
        return view('Data_Menu.create', compact('bahan_baku', 'kategori'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'nama_menu' => 'required|string|max:255',
            'harga' => 'required|numeric',
            'ketersediaan' => 'required|boolean',
            'kategori_id' => 'required|exists:kategori,id', 
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi gambar
            'resep' => 'nullable|array',
            'resep.*.bahan_baku_id' => 'required_with:resep|exists:bahan_baku,id',
            'resep.*.jumlah_dibutuhkan' => 'required_with:resep|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        DB::transaction(function () use ($request) {
            $imagePath = null;
            if ($request->hasFile('image')) {
                // Simpan gambar ke storage/app/public/menu-images
                $imagePath = $request->file('image')->store('menu-images', 'public');
            }

            $menu = Menu::create([
                'nama_menu' => $request->nama_menu,
                'harga' => $request->harga,
                'kategori_id' => $request->kategori_id,
                'image' => $imagePath, // Simpan path gambar
                'ketersediaan' => $request->ketersediaan,
            ]);

            // Simpan resep jika ada
            if ($request->has('resep')) {
                foreach ($request->resep as $item) {
                    ResepMenu::create([
                        'menu_id' => $menu->id,
                        'bahan_baku_id' => $item['bahan_baku_id'],
                        'jumlah_dibutuhkan' => $item['jumlah_dibutuhkan'],
                    ]);
                }
            }
        });

        return redirect()->route('owner.menu')->with('success', 'Menu berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit menu.
     */
    public function edit($id)
    {
        $menu = Menu::with('resep')->findOrFail($id);
        $bahan_baku = BahanBaku::orderBy('nama_bahan')->get();
        $kategori = Kategori::orderBy('nama_kategori')->get();
        return view('Data_Menu.edit', compact('menu', 'bahan_baku', 'kategori'));
    }

    /**
     * Memperbarui data menu di database.
     */
    public function update(Request $request, $id)
    {
        // ... validasi sama seperti store ...

        DB::transaction(function () use ($request, $id) {
            $menu = Menu::findOrFail($id);
            
            $imagePath = $menu->image;
            if ($request->hasFile('image')) {
                // Hapus gambar lama jika ada
                if ($menu->image) {
                    Storage::disk('public')->delete($menu->image);
                }
                // Upload gambar baru
                $imagePath = $request->file('image')->store('menu-images', 'public');
            }

            $menu->update([
                'nama_menu' => $request->nama_menu,
                'harga' => $request->harga,
                'image' => $imagePath, // Update dengan path gambar baru atau yang lama
                'kategori_id' => $request->kategori_id,
                'ketersediaan' => $request->ketersediaan,
            ]);

            // Hapus resep lama
            $menu->resep()->delete();

            // Buat ulang resep baru
            if ($request->has('resep')) {
                foreach ($request->resep as $item) {
                    ResepMenu::create([
                        'menu_id' => $menu->id,
                        'bahan_baku_id' => $item['bahan_baku_id'],
                        'jumlah_dibutuhkan' => $item['jumlah_dibutuhkan'],
                    ]);
                }
            }
        });
        
        return redirect()->route('owner.menu')->with('success', 'Menu berhasil diperbarui.');
    }

    public function show($id)
    {
        // Ambil data menu dan relasi 'resep', lalu di dalam resep ambil juga relasi 'bahanBaku'
        $menu = Menu::with('resep.bahanBaku')->findOrFail($id);
        
        // Kirim data ke view baru bernama 'show.blade.php'
        return view('Data_Menu.show', compact('menu'));
    }

    /**
     * Menghapus data menu dari database.
     */
    public function delete($id)
    {
        $data = Menu::findOrFail($id);
        $data->delete();

        return redirect()->route('owner.menu');
    }
}

