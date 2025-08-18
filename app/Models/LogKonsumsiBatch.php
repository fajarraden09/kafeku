<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogKonsumsiBatch extends Model
{
    use HasFactory;
    protected $table = 'log_konsumsi_batch';
    protected $fillable = ['detail_transaksi_id', 'batch_bahan_baku_id', 'jumlah_diambil'];
}