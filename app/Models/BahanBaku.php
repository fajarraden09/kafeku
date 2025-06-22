<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BahanBaku extends Model
{
    use HasFactory;

    /**
     * Menentukan nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'bahan_baku';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
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
}