<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    /**
     * HANYA ADA SATU DEKLARASI $fillable
     * Pastikan 'image' sudah termasuk di dalamnya.
     */
    protected $fillable = [
        'nama_menu',
        'harga',
        'image',
        'kategori_id',
        'ketersediaan',
    ];

        public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function resep()
    {
        return $this->hasMany(ResepMenu::class, 'menu_id');
    }
}