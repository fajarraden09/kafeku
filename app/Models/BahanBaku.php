<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BahanBakuKeluar;

class BahanBaku extends Model
{
    use HasFactory;

    protected $table = 'bahan_baku';

    protected $casts = [
        'notifikasi_stok_terakhir_dikirim_at' => 'datetime',
        // ... pastikan cast lain yang sudah ada tidak terhapus ...
    ];

    protected $fillable = [
        'nama_bahan',
        'stok',
        'satuan',
        'batas_minimum',
        'notifikasi_terkirim',
    ];

    public function resep()
    {
        return $this->hasMany(ResepMenu::class);
    }

    /**
     * Mendefinisikan relasi "hasMany" ke model BatchBahanBaku.
     */
    public function batches()
    {
        return $this->hasMany(BatchBahanBaku::class, 'bahan_baku_id');
    }
    public function bahanKeluar()
    {
        return $this->hasMany(BahanBakuKeluar::class, 'bahan_baku_id');
    }
}
