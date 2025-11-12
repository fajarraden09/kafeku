<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Kategori;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        $kategori = [
            'Berat',
            'Ringan',
            'Kopi',
            'Non-Kopi',
            'Gojek', // <-- TAMBAHKAN BARIS INI
        ];

        foreach ($kategori as $namaKategori) {
            // Gunakan firstOrCreate untuk menghindari duplikat
            Kategori::firstOrCreate(['nama_kategori' => $namaKategori]); 
        }
    }
}