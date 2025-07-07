<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BahanBaku extends Model
{
    use HasFactory;

    protected $table = 'bahan_baku';

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
}
