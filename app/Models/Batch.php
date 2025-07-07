<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use HasFactory;

    // Tentukan nama tabel jika tidak mengikuti konvensi penamaan Laravel (plural dari nama model)
    // Jika nama tabel Anda 'batches', baris ini tidak wajib tapi bagus untuk eksplisit
    protected $table = 'batches';

    // Atribut yang bisa diisi secara massal
    protected $fillable = [
        'bahan_baku_id', // Foreign key ke tabel bahan_baku
        'jumlah_awal',   // Jumlah saat batch masuk (atau sisa stok batch)
        'tanggal_kadaluarsa',
        'user_id',       // ID user yang mencatat (opsional, jika Anda punya relasi user)
        // tambahkan kolom lain yang relevan di tabel 'batches' Anda
    ];

    // Casts untuk otomatis mengubah tipe data
    protected $casts = [
        'tanggal_kadaluarsa' => 'date',
    ];

    // Definisi relasi (penting untuk mengakses nama bahan dan user)

    // Relasi ke BahanBaku (bahan baku yang terkait dengan batch ini)
    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }

    // Relasi ke User (jika ada user yang mencatat batch ini)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id'); // Pastikan User::class mengarah ke model User Anda
    }
}