<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchBahanBaku extends Model
{
    use HasFactory;

    // Menentukan nama tabel secara eksplisit
    protected $table = 'batch_bahan_baku';

    // Kolom yang boleh diisi secara massal
    protected $fillable = [
        'bahan_baku_id',
        'user_id',
        'kode_batch',
        'jumlah_awal',
        'sisa_stok',
        'tanggal_masuk',
        'tanggal_kadaluarsa',
    ];

    /**
     * Mendefinisikan relasi "belongsTo" ke model BahanBaku.
     * Satu batch pasti dimiliki oleh satu jenis bahan baku.
     */
    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }

    /**
     * Relasi ke model User (siapa yang mencatat).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}