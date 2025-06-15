<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResepMenu extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'resep_menu';

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'menu_id', // <-- TAMBAHKAN BARIS INI
        'bahan_baku_id',
        'jumlah_dibutuhkan',
    ];

    /**
     * Relasi ke model BahanBaku.
     */
    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }

    /**
     * Relasi ke model Menu.
     */
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }
}