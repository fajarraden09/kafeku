<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailTransaksi extends Model
{
    use HasFactory;
    protected $table = 'detail_transaksi';
    protected $fillable = ['transaksi_id', 'menu_id', 'jumlah', 'harga_saat_transaksi', 'subtotal'];

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }
        public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id');
    }
}