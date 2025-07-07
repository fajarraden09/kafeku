<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchBahanBaku extends Model
{
    use HasFactory;

    protected $table = 'batch_bahan_baku'; // Pastikan ini sesuai dengan nama tabel Anda

    protected $fillable = [
        'bahan_baku_id',
        'user_id',
        'kode_batch',
        'jumlah_awal',
        'sisa_stok',
        'tanggal_masuk',
        'tanggal_kadaluarsa',
    ];

    protected $casts = [
        'tanggal_masuk'      => 'date',
        'tanggal_kadaluarsa' => 'date',
    ];

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
