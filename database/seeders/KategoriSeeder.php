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
        ];

        foreach ($kategori as $namaKategori) {
            Kategori::create(['nama_kategori' => $namaKategori]);
        }
    }
}