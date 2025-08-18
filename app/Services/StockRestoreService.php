<?php

namespace App\Services;

use App\Models\DetailTransaksi;
use App\Models\LogKonsumsiBatch;
use App\Models\BatchBahanBaku;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockRestoreService
{
    /**
     * Mengembalikan stok bahan baku berdasarkan log konsumsi yang tercatat.
     *
     * @param DetailTransaksi $detailTransaksi Detail transaksi yang dibatalkan.
     */
    public static function restore(DetailTransaksi $detailTransaksi): void
    {
        Log::info("--- Memulai StockRestoreService untuk DetailTransaksi ID: {$detailTransaksi->id} ---");

        // Cari semua log konsumsi yang terkait dengan detail transaksi ini
        $logs = LogKonsumsiBatch::where('detail_transaksi_id', $detailTransaksi->id)->get();

        if ($logs->isEmpty()) {
            Log::warning("Tidak ditemukan log konsumsi untuk DetailTransaksi ID: {$detailTransaksi->id}. Mungkin tidak ada bahan baku yang digunakan.");
            return;
        }

        DB::transaction(function () use ($logs) {
            foreach ($logs as $log) {
                // Temukan batch yang stoknya akan dikembalikan
                $batch = BatchBahanBaku::lockForUpdate()->find($log->batch_bahan_baku_id);
                if (!$batch) continue;

                // Kembalikan stok ke batch
                $batch->increment('sisa_stok', $log->jumlah_diambil);

                // Kembalikan stok total ke bahan baku utama
                $batch->bahanBaku()->increment('stok', $log->jumlah_diambil);

                Log::info("Mengembalikan {$log->jumlah_diambil} ke Batch ID: {$batch->id} ({$batch->bahanBaku->nama_bahan}).");
                
                // Hapus log setelah selesai
                $log->delete();
            }
        });
    }
}