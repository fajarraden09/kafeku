<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\BatchBahanBaku;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Tambahkan ini: Import facade Log
use Throwable;

class StockConsumptionService
{
    /**
     * Mengurangi stok bahan baku berdasarkan logika First-Expired, First-Out (FEFO).
     *
     * @param BahanBaku $bahanBaku Objek BahanBaku yang stoknya akan dikurangi.
     * @param float $quantity Jumlah bahan baku yang akan dikurangi.
     * @return bool True jika pengurangan stok berhasil, false jika tidak.
     * @throws \Exception Jika stok tidak cukup atau terjadi kesalahan lain.
     */
    public static function consume(BahanBaku $bahanBaku, float $quantity): bool
    {
        // Pastikan kuantitas yang diminta tidak negatif atau nol
        if ($quantity <= 0) {
            return true; // Tidak ada yang perlu dikurangi
        }

        // Pastikan stok total di BahanBaku mencukupi sebelum mencoba mengurangi dari batch
        if ($bahanBaku->stok < $quantity) {
            throw new \Exception("Stok bahan baku '{$bahanBaku->nama_bahan}' tidak mencukupi untuk konsumsi. Dibutuhkan: {$quantity}, Tersedia: {$bahanBaku->stok}");
        }

        // Tidak perlu DB::beginTransaction() di sini karena sudah ditangani di TransaksiController
        try {
            $remainingQuantityToConsume = $quantity;

            // Ambil semua batch bahan baku yang masih memiliki sisa stok untuk bahan baku ini,
            // diurutkan berdasarkan tanggal kadaluarsa terdekat (FEFO).
            // Jika tanggal kadaluarsa sama, urutkan berdasarkan tanggal masuk (FIFO).
            $batches = $bahanBaku->batches()
                                ->where('sisa_stok', '>', 0)
                                ->whereNotNull('tanggal_kadaluarsa')
                                ->orderBy('tanggal_kadaluarsa', 'asc')
                                ->orderBy('created_at', 'asc')
                                ->lockForUpdate()
                                ->get();

            foreach ($batches as $batch) {
                if ($remainingQuantityToConsume <= 0) {
                    break;
                }

                $deductAmount = min($remainingQuantityToConsume, $batch->sisa_stok);

                $batch->sisa_stok -= $deductAmount;
                $batch->save();

                $remainingQuantityToConsume -= $deductAmount;
            }

            if ($remainingQuantityToConsume > 0) {
                throw new \Exception("Inkonsistensi stok: Tidak dapat mengurangi {$remainingQuantityToConsume} unit dari batch yang tersedia untuk '{$bahanBaku->nama_bahan}'.");
            }

            $bahanBaku->decrement('stok', $quantity);

            // Tidak perlu DB::commit() di sini karena sudah ditangani di TransaksiController
            return true;

        } catch (Throwable $e) {
            // Tidak perlu DB::rollBack() di sini karena sudah ditangani di TransaksiController
            Log::error("Gagal mengurangi stok bahan baku '{$bahanBaku->nama_bahan}' ({$quantity} unit) di StockConsumptionService: " . $e->getMessage());
            throw $e;
        }
    }
}
