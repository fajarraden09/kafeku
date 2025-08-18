<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\User;
use App\Mail\LowStockNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class StockNotificationService
{
    const NOTIFICATION_INTERVAL_DAYS = 4; // Interval pengiriman notifikasi dalam hari

    /**
     * Memeriksa apakah notifikasi boleh dikirim berdasarkan interval waktu.
     *
     * @param Carbon|null $lastSentDate Tanggal terakhir notifikasi dikirim.
     * @return bool
     */
    private static function canSendNotification(?Carbon $lastSentDate): bool
    {
        if (is_null($lastSentDate)) {
            return true; // Boleh kirim jika belum pernah dikirim sebelumnya.
        }
        // Boleh kirim jika selisih hari sejak pengiriman terakhir >= interval.
        return Carbon::now()->diffInDays($lastSentDate) >= self::NOTIFICATION_INTERVAL_DAYS;
    }

    /**
     * Memeriksa status stok dan kadaluarsa, lalu mengirim notifikasi jika perlu.
     *
     * @param BahanBaku $bahanBaku
     * @return void
     */
    public static function checkAndNotify(BahanBaku $bahanBaku): void
    {
        Log::info("--- Memulai checkAndNotify untuk BahanBaku: {$bahanBaku->nama_bahan} (ID: {$bahanBaku->id}) ---");
        $bahanBaku->load('batches');

        // --- Notifikasi Stok Total (Habis / Rendah) ---
        $canSendStockNotification = self::canSendNotification($bahanBaku->notifikasi_stok_terakhir_dikirim_at);

        if ($bahanBaku->stok <= 0) {
            Log::info("Kondisi: Stok Habis ({$bahanBaku->stok} {$bahanBaku->satuan})");
            if ($canSendStockNotification) {
                self::sendNotification(
                    $bahanBaku,
                    'Stok Habis: ' . $bahanBaku->nama_bahan,
                    'Stok bahan baku "' . $bahanBaku->nama_bahan . '" telah habis.'
                );
                $bahanBaku->notifikasi_stok_terakhir_dikirim_at = Carbon::now();
                $bahanBaku->save();
            } else {
                Log::info("Notifikasi stok habis untuk {$bahanBaku->nama_bahan} ditunda karena interval belum 5 hari.");
            }
        }
        elseif ($bahanBaku->stok > 0 && $bahanBaku->stok <= $bahanBaku->batas_minimum) {
            Log::info("Kondisi: Stok Rendah ({$bahanBaku->stok} {$bahanBaku->satuan}, Batas Min: {$bahanBaku->batas_minimum})");
            if ($canSendStockNotification) {
                self::sendNotification(
                    $bahanBaku,
                    'Stok Rendah: ' . $bahanBaku->nama_bahan,
                    'Stok bahan baku "' . $bahanBaku->nama_bahan . '" hampir habis. Sisa: ' . $bahanBaku->stok . ' ' . $bahanBaku->satuan . '.'
                );
                $bahanBaku->notifikasi_stok_terakhir_dikirim_at = Carbon::now();
                $bahanBaku->save();
            } else {
                Log::info("Notifikasi stok rendah untuk {$bahanBaku->nama_bahan} ditunda karena interval belum 5 hari.");
            }
        }
        else {
            Log::info("Kondisi: Stok Aman ({$bahanBaku->stok} {$bahanBaku->satuan}, Batas Min: {$bahanBaku->batas_minimum})");
            // Reset timestamp jika stok sudah aman agar notifikasi bisa langsung terkirim lagi saat stok rendah.
            if ($bahanBaku->notifikasi_stok_terakhir_dikirim_at !== null) {
                $bahanBaku->notifikasi_stok_terakhir_dikirim_at = null;
                $bahanBaku->save();
                Log::info('Timestamp notifikasi stok untuk "' . $bahanBaku->nama_bahan . '" direset.');
            }
        }

        // --- Notifikasi Kadaluarsa (berdasarkan batch) ---
        $today = Carbon::now();
        foreach ($bahanBaku->batches as $batch) {
            if (!$batch->tanggal_kadaluarsa || $batch->sisa_stok <= 0) {
                continue; // Lewati jika tidak ada tanggal kadaluarsa atau stok batch habis
            }

            $selisihHari = $today->diffInDays($batch->tanggal_kadaluarsa, false);
            $canSendExpiryNotification = self::canSendNotification($batch->notifikasi_kadaluarsa_terakhir_dikirim_at);

            // Kondisi: Sudah Kadaluarsa
            if ($selisihHari < 0) {
                Log::info("Kondisi: Batch ID {$batch->id} Kadaluarsa.");
                if ($canSendExpiryNotification) {
                    self::sendNotification(
                        $bahanBaku,
                        'Batch Kadaluarsa: ' . $bahanBaku->nama_bahan,
                        'Batch bahan baku "' . $bahanBaku->nama_bahan . '" (ID: ' . $batch->id . ') telah kadaluarsa pada ' . $batch->tanggal_kadaluarsa->format('d-m-Y') . '. Sisa stok: ' . $batch->sisa_stok . ' ' . $bahanBaku->satuan . '.'
                    );
                    $batch->notifikasi_kadaluarsa_terakhir_dikirim_at = Carbon::now();
                    $batch->save();
                } else {
                    Log::info("  - Notifikasi kadaluarsa untuk Batch ID {$batch->id} ditunda (interval belum 5 hari).");
                }
            }
            // Kondisi: Hampir Kadaluarsa (misal, dalam 15 hari)
            elseif ($selisihHari <= 15) {
                Log::info("Kondisi: Batch ID {$batch->id} Hampir Kadaluarsa.");
                if ($canSendExpiryNotification) {
                    $selisihHariBulat = (int) ceil($selisihHari);
                    self::sendNotification(
                        $bahanBaku,
                        'Batch Hampir Kadaluarsa: ' . $bahanBaku->nama_bahan,
                        'Batch bahan baku "' . $bahanBaku->nama_bahan . '" (ID: ' . $batch->id . ') akan kadaluarsa dalam ' . $selisihHariBulat . ' hari (pada ' . $batch->tanggal_kadaluarsa->format('d-m-Y') . '). Sisa stok: ' . $batch->sisa_stok . ' ' . $bahanBaku->satuan . '.'
                    );
                    $batch->notifikasi_kadaluarsa_terakhir_dikirim_at = Carbon::now();
                    $batch->save();
                } else {
                    Log::info("  - Notifikasi hampir kadaluarsa untuk Batch ID {$batch->id} ditunda (interval belum 5 hari).");
                }
            }
        }
        Log::info("--- Selesai checkAndNotify untuk BahanBaku: {$bahanBaku->nama_bahan} ---");
    }

    /**
     * Metode untuk mengirimkan notifikasi.
     * (Tidak ada perubahan di sini)
     */
    private static function sendNotification(BahanBaku $bahanBaku, string $subject, string $message): void
    {
        Log::warning("[NOTIFIKASI STOK] {$subject}: {$message}");
        $owners = User::where('role', 'owner')->get();

        if ($owners->isNotEmpty()) {
            foreach ($owners as $owner) {
                if ($owner->email) {
                    try {
                        Mail::to($owner->email)->send(new LowStockNotification($bahanBaku, $subject, $message));
                        Log::info("Notifikasi email untuk '{$subject}' berhasil dikirim ke {$owner->email}.");
                    } catch (\Exception $e) {
                        Log::error("Gagal mengirim email notifikasi '{$subject}' ke {$owner->email}: " . $e->getMessage());
                    }
                }
                if ($owner->phone_number && env('FONNTE_API_TOKEN')) {
                    try {
                        Http::withHeaders(['Authorization' => env('FONNTE_API_TOKEN')])
                            ->post('https://api.fonnte.com/send', [
                                'target' => $owner->phone_number,
                                'message' => $message
                            ]);
                        Log::info("Notifikasi WhatsApp untuk '{$subject}' berhasil dikirim ke {$owner->phone_number}.");
                    } catch (\Exception | \GuzzleHttp\Exception\GuzzleException $e) {
                        Log::error("Gagal mengirim notifikasi WhatsApp '{$subject}' ke {$owner->phone_number}: " . $e->getMessage());
                    }
                }
            }
        } else {
            Log::warning("Tidak ada user dengan role 'owner' ditemukan untuk mengirim notifikasi.");
        }
    }
}