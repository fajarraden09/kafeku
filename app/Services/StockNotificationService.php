<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\User; // Tetap diperlukan jika Anda mengirim email/WhatsApp ke User
use App\Mail\LowStockNotification; // Tetap diperlukan jika Anda mengirim email
use Illuminate\Support\Facades\Mail; // Tetap diperlukan jika Anda mengirim email
use Illuminate\Support\Facades\Http; // Tetap diperlukan jika Anda mengirim WhatsApp
use Illuminate\Support\Facades\Log;
use Carbon\Carbon; // Tambahkan ini: Import Carbon untuk manipulasi tanggal

class StockNotificationService
{
    /**
     * Memeriksa status stok bahan baku dan mengirimkan notifikasi jika diperlukan.
     *
     * @param BahanBaku $bahanBaku Objek BahanBaku yang akan diperiksa.
     * @return void
     */
    public static function checkAndNotify(BahanBaku $bahanBaku): void
    {
        // Muat ulang relasi batches untuk mendapatkan data tanggal kadaluarsa terbaru
        // Ini penting karena BahanBaku yang masuk mungkin belum memuat relasi ini
        $bahanBaku->load('batches');

        // --- Notifikasi Stok Total (Habis / Rendah) ---
        // 1. Notifikasi Stok Habis
        if ($bahanBaku->stok <= 0) {
            self::sendNotification(
                $bahanBaku, // Pass $bahanBaku here
                'Stok Habis: ' . $bahanBaku->nama_bahan,
                'Stok bahan baku "' . $bahanBaku->nama_bahan . '" telah habis.'
            );
            // Tandai notifikasi sudah terkirim untuk stok total
            $bahanBaku->notifikasi_terkirim = true;
            $bahanBaku->save();
        }
        // 2. Notifikasi Stok Rendah (Hampir Habis) - hanya jika stok > 0 dan di bawah batas minimum
        elseif ($bahanBaku->stok > 0 && $bahanBaku->stok <= $bahanBaku->batas_minimum) {
            // Kirim notifikasi HANYA JIKA notifikasi belum pernah terkirim untuk kondisi ini
            if (!$bahanBaku->notifikasi_terkirim) {
                self::sendNotification(
                    $bahanBaku, // Pass $bahanBaku here
                    'Stok Rendah: ' . $bahanBaku->nama_bahan,
                    'Stok bahan baku "' . $bahanBaku->nama_bahan . '" hampir habis. Sisa: ' . $bahanBaku->stok . ' ' . $bahanBaku->satuan . '.'
                );
                // Tandai notifikasi sudah terkirim untuk stok total
                $bahanBaku->notifikasi_terkirim = true;
                $bahanBaku->save();
            }
        }
        // 3. Reset Notifikasi Stok jika sudah Aman (stok > batas_minimum)
        else {
            self::resetNotificationFlag($bahanBaku);
        }

        // --- Notifikasi Kadaluarsa (berdasarkan batch) ---
        $today = Carbon::now();

        foreach ($bahanBaku->batches as $batch) {
            if ($batch->tanggal_kadaluarsa) {
                $selisihHari = $today->diffInDays($batch->tanggal_kadaluarsa, false);

                // Notifikasi Kadaluarsa
                if ($selisihHari < 0 && $batch->sisa_stok > 0) { // Hanya notifikasi jika masih ada stok di batch
                    self::sendNotification(
                        $bahanBaku, // Pass $bahanBaku here
                        'Batch Kadaluarsa: ' . $bahanBaku->nama_bahan,
                        'Batch bahan baku "' . $bahanBaku->nama_bahan . '" (ID Batch: ' . $batch->id . ') telah kadaluarsa pada ' . $batch->tanggal_kadaluarsa->format('d-m-Y') . '. Sisa stok: ' . $batch->sisa_stok . ' ' . $bahanBaku->satuan . '.'
                    );
                    // Anda bisa menambahkan flag di tabel batch_bahan_baku jika ingin notifikasi ini hanya sekali per batch
                    // Contoh: $batch->notifikasi_kadaluarsa_terkirim = true; $batch->save();
                }
                // Notifikasi Hampir Kadaluarsa (misal: dalam 30 hari ke depan)
                elseif ($selisihHari >= 0 && $selisihHari <= 30 && $batch->sisa_stok > 0) { // Hanya notifikasi jika masih ada stok di batch
                    self::sendNotification(
                        $bahanBaku, // Pass $bahanBaku here
                        'Batch Hampir Kadaluarsa: ' . $bahanBaku->nama_bahan,
                        'Batch bahan baku "' . $bahanBaku->nama_bahan . '" (ID Batch: ' . $batch->id . ') akan kadaluarsa dalam ' . $selisihHari . ' hari (pada ' . $batch->tanggal_kadaluarsa->format('d-m-Y') . '). Sisa stok: ' . $batch->sisa_stok . ' ' . $bahanBaku->satuan . '.'
                    );
                    // Anda bisa menambahkan flag di tabel batch_bahan_baku jika ingin notifikasi ini hanya sekali per batch
                    // Contoh: $batch->notifikasi_hampir_kadaluarsa_terkirim = true; $batch->save();
                }
            }
        }
    }

    /**
     * Mengatur ulang flag notifikasi 'notifikasi_terkirim' menjadi false
     * jika stok sudah kembali di atas batas minimum.
     *
     * @param BahanBaku $bahanBaku
     * @return void
     */
    public static function resetNotificationFlag(BahanBaku $bahanBaku): void
    {
        if ($bahanBaku->stok > $bahanBaku->batas_minimum && $bahanBaku->notifikasi_terkirim) {
            $bahanBaku->notifikasi_terkirim = false;
            $bahanBaku->save();
            Log::info('Flag notifikasi untuk "' . $bahanBaku->nama_bahan . '" direset (stok aman).');
        }
    }

    /**
     * Metode untuk mengirimkan notifikasi.
     * Anda bisa mengembangkan ini untuk mengirim email, notifikasi dashboard, dll.
     * Saat ini akan mencoba mengirim email/WhatsApp (jika konfigurasi ada) dan mencatat ke log Laravel.
     *
     * @param BahanBaku $bahanBaku Objek BahanBaku yang terkait dengan notifikasi.
     * @param string $subject Subjek notifikasi.
     * @param string $message Isi notifikasi.
     * @return void
     */
    private static function sendNotification(BahanBaku $bahanBaku, string $subject, string $message): void // Added $bahanBaku parameter
    {
        Log::warning("[NOTIFIKASI STOK] {$subject}: {$message}");

        // Dapatkan owner untuk pengiriman notifikasi
        $owner = User::where('role', 'owner')->first();

        if ($owner) {
            // 1. Kirim Email
            if ($owner->email) {
                try {
                    // Asumsi Anda memiliki Mailable LowStockNotification yang bisa menerima subject dan message
                    // Jika tidak, Anda perlu memodifikasi Mailable tersebut atau membuat Mailable baru
                    Mail::to($owner->email)->send(new LowStockNotification($bahanBaku, $subject, $message)); // Contoh: passing subject dan message
                    Log::info("Notifikasi email untuk '{$subject}' berhasil dikirim ke {$owner->email}.");
                } catch (\Exception $e) {
                    Log::error("Gagal mengirim email notifikasi '{$subject}': " . $e->getMessage());
                }
            }

            // 2. Kirim WhatsApp (Contoh menggunakan Fonnte)
            if ($owner->phone_number && env('FONNTE_API_TOKEN')) {
                try {
                    Http::withHeaders(['Authorization' => env('FONNTE_API_TOKEN')])
                        ->post('https://api.fonnte.com/send', [
                            'target' => $owner->phone_number,
                            'message' => $message
                        ]);
                    Log::info("Notifikasi WhatsApp untuk '{$subject}' berhasil dikirim.");
                } catch (\Exception $e) {
                    Log::error("Gagal mengirim notifikasi WhatsApp '{$subject}': " . $e->getMessage());
                }
            }
        }
    }
}
