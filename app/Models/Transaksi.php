<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 

class Transaksi extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'transaksi';
    
    protected $fillable = [
        'kode_transaksi',
        'user_id', 
        'nama_pelanggan',
        'total_harga', 
        'metode_pembayaran',
        'status_pembayaran', 
    ];

    /**
     * Relasi ke detail transaksi (satu transaksi punya banyak detail).
     */
    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class);
    }

    /**
     * Relasi ke user (satu transaksi dimiliki/dicatat oleh satu user).
     * INI ADALAH FUNGSI YANG HILANG DAN PERLU ANDA TAMBAHKAN.
     */
    public function user()
    {
        // Relasi belongsTo berarti "dimiliki oleh".
        // 'user_id' adalah foreign key di tabel 'transaksi' ini.
        return $this->belongsTo(User::class, 'user_id');
    }
    
}