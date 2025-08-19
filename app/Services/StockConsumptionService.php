<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\LogKonsumsiBatch;
use Illuminate\Support\Facades\Log;

class StockConsumptionService
{
    /**
     * Mengurangi stok bahan baku (FEFO) dan secara opsional mencatat log konsumsi.
     *
     * @param BahanBaku $bahanBaku Objek BahanBaku yang stoknya akan dikurangi.
     * @param float $quantity Jumlah bahan baku yang akan dikurangi.
     * @param int|null $detailTransaksiId ID detail transaksi (opsional), hanya untuk logging.
     * @return void
     * @throws \Exception Jika stok tidak cukup.
     */
    public static function consume(BahanBaku $bahanBaku, float $quantity, ?int $detailTransaksiId = null): void
    {
        if ($quantity <= 0) {
            return; // Tidak ada yang perlu dikurangi
        }

        if ($bahanBaku->stok < $quantity) {
            throw new \Exception("Stok '{$bahanBaku->nama_bahan}' tidak mencukupi. Dibutuhkan: {$quantity}, Tersedia: {$bahanBaku->stok}");
        }

        try {
            $remainingQuantityToConsume = $quantity;

            $batches = $bahanBaku->batches()
                                ->where('sisa_stok', '>', 0)
                                ->whereNotNull('tanggal_kadaluarsa')
                                ->orderBy('tanggal_kadaluarsa', 'asc')
                                ->orderBy('created_at', 'asc')
                                ->lockForUpdate() // Mengunci baris untuk mencegah race condition
                                ->get();

            foreach ($batches as $batch) {
                if ($remainingQuantityToConsume <= 0) {
                    break;
                }

                $deductAmount = min($remainingQuantityToConsume, $batch->sisa_stok);

                // Kurangi stok dari batch
                $batch->decrement('sisa_stok', $deductAmount);

                // Hanya buat log jika detailTransaksiId diberikan (bukan null).
                if ($detailTransaksiId !== null) {
                    LogKonsumsiBatch::create([
                        'detail_transaksi_id' => $detailTransaksiId,
                        'batch_bahan_baku_id' => $batch->id,
                        'jumlah_diambil'      => $deductAmount,
                    ]);
                }

                $remainingQuantityToConsume -= $deductAmount;
            }
            
            // Kurangi stok total di tabel bahan baku
            $bahanBaku->decrement('stok', $quantity);

        } catch (\Throwable $e) {
            Log::error("Gagal mengurangi stok '{$bahanBaku->nama_bahan}': " . $e->getMessage());
            // Lemparkan kembali error agar transaksi di Controller bisa di-rollback
            throw $e;
        }
    }
}