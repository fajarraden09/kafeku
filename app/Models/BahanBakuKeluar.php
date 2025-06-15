<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BahanBakuKeluar extends Model
{
    use HasFactory;

    // Menentukan nama tabel secara eksplisit
    protected $table = 'bahan_baku_keluar';

    // Kolom yang boleh diisi secara massal
    protected $fillable = [
        'bahan_baku_id',
        'user_id',
        'batch_id',
        'jumlah_keluar',
        'keterangan',
        'tanggal_keluar',
    ];

    /**
     * Relasi ke model BahanBaku (bahan apa yang keluar)
     */
    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }

    /**
     * Relasi ke model User (siapa yang mencatat)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}