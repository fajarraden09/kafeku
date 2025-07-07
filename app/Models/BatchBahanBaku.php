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
        'tanggal_masuk', // Pastikan kolom ini ada di tabel Anda jika digunakan
        'tanggal_kadaluarsa',
    ];

    /**
     * Atribut yang harus di-cast ke tipe data tertentu.
     * Mengubah kolom tanggal menjadi objek Carbon secara otomatis.
     *
     * @var array
     */
    protected $casts = [
        'tanggal_masuk'      => 'date', // Asumsi kolom ini ada di tabel Anda
        'tanggal_kadaluarsa' => 'date',
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
