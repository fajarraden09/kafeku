<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\User; // Diperlukan untuk mencari user owner
use App\Mail\LowStockNotification; // Diperlukan untuk mengirim email
use Illuminate\Support\Facades\Mail; // Diperlukan untuk mengirim email
use Illuminate\Support\Facades\Http; // Diperlukan untuk mengirim WhatsApp
use Illuminate\Support\Facades\Log; // Diperlukan untuk logging
use Carbon\Carbon; // Diperlukan untuk manipulasi tanggal

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
        Log::info("--- Memulai checkAndNotify untuk BahanBaku: {$bahanBaku->nama_bahan} (ID: {$bahanBaku->id}) ---");

        // Muat ulang relasi batches untuk mendapatkan data tanggal kadaluarsa terbaru
        // Ini penting karena BahanBaku yang masuk mungkin belum memuat relasi ini
        $bahanBaku->load('batches');
        Log::info("Jumlah batch ditemukan untuk {$bahanBaku->nama_bahan}: {$bahanBaku->batches->count()}");


        // --- Notifikasi Stok Total (Habis / Rendah) ---
        // 1. Notifikasi Stok Habis
        if ($bahanBaku->stok <= 0) {
            Log::info("Kondisi: Stok Habis ({$bahanBaku->stok} {$bahanBaku->satuan})");
            self::sendNotification(
                $bahanBaku,
                'Stok Habis: ' . $bahanBaku->nama_bahan,
                'Stok bahan baku "' . $bahanBaku->nama_bahan . '" telah habis.'
            );
            $bahanBaku->notifikasi_terkirim = true;
            $bahanBaku->save();
        }
        // 2. Notifikasi Stok Rendah (Hampir Habis) - hanya jika stok > 0 dan di bawah batas minimum
        elseif ($bahanBaku->stok > 0 && $bahanBaku->stok <= $bahanBaku->batas_minimum) {
            Log::info("Kondisi: Stok Rendah ({$bahanBaku->stok} {$bahanBaku->satuan}, Batas Min: {$bahanBaku->batas_minimum})");
            // Kirim notifikasi HANYA JIKA notifikasi belum pernah terkirim untuk kondisi ini
            if (!$bahanBaku->notifikasi_terkirim) {
                self::sendNotification(
                    $bahanBaku,
                    'Stok Rendah: ' . $bahanBaku->nama_bahan,
                    'Stok bahan baku "' . $bahanBaku->nama_bahan . '" hampir habis. Sisa: ' . $bahanBaku->stok . ' ' . $bahanBaku->satuan . '.'
                );
                $bahanBaku->notifikasi_terkirim = true;
                $bahanBaku->save();
            } else {
                Log::info("Notifikasi stok rendah sudah terkirim sebelumnya untuk {$bahanBaku->nama_bahan}. Tidak mengirim lagi.");
            }
        }
        // 3. Reset Notifikasi Stok jika sudah Aman (stok > batas_minimum)
        else {
            Log::info("Kondisi: Stok Aman ({$bahanBaku->stok} {$bahanBaku->satuan}, Batas Min: {$bahanBaku->batas_minimum})");
            self::resetNotificationFlag($bahanBaku);
        }

        // --- Notifikasi Kadaluarsa (berdasarkan batch) ---
        $today = Carbon::now();
        Log::info("Tanggal Hari Ini: " . $today->format('Y-m-d'));

        foreach ($bahanBaku->batches as $batch) {
            Log::info("Memeriksa Batch ID: {$batch->id} untuk {$bahanBaku->nama_bahan}");
            Log::info("  - Tanggal Kadaluarsa Batch: " . ($batch->tanggal_kadaluarsa ? $batch->tanggal_kadaluarsa->format('Y-m-d') : 'NULL'));
            Log::info("  - Sisa Stok Batch: {$batch->sisa_stok}");

            if ($batch->tanggal_kadaluarsa) {
                $selisihHari = $today->diffInDays($batch->tanggal_kadaluarsa, false);
                $selisihHariBulat = (int) ceil($today->diffInDays($batch->tanggal_kadaluarsa, false)); // Membulatkan ke atas untuk hari

                Log::info("  - Selisih Hari ke Kadaluarsa: {$selisihHari} hari (Bulat: {$selisihHariBulat}).");

                // Notifikasi Kadaluarsa
                if ($selisihHari < 0 && $batch->sisa_stok > 0) {
                    Log::info("Kondisi: Batch Kadaluarsa.");
                    self::sendNotification(
                        $bahanBaku,
                        'Batch Kadaluarsa: ' . $bahanBaku->nama_bahan,
                        'Batch bahan baku "' . $bahanBaku->nama_bahan . '" (ID Batch: ' . $batch->id . ') telah kadaluarsa pada ' . $batch->tanggal_kadaluarsa->format('d-m-Y') . '. Terdapat ' . $batch->sisa_stok . ' ' . $bahanBaku->satuan . ' stok yang kadaluarsa di batch ini.'
                    );
                    // Pertimbangkan untuk menambahkan flag di tabel batch_bahan_baku (misal: notifikasi_kadaluarsa_terkirim)
                    // agar notifikasi ini hanya terkirim sekali per batch yang kadaluarsa.
                }
                // Notifikasi Hampir Kadaluarsa (misal: dalam 15 hari ke depan)
                elseif ($selisihHari >= 0 && $selisihHari <= 15 && $batch->sisa_stok > 0) {
                    Log::info("Kondisi: Batch Hampir Kadaluarsa.");
                    self::sendNotification(
                        $bahanBaku,
                        'Batch Hampir Kadaluarsa: ' . $bahanBaku->nama_bahan,
                        'Batch bahan baku "' . $bahanBaku->nama_bahan . '" (ID Batch: ' . $batch->id . ') akan kadaluarsa dalam ' . $selisihHariBulat . ' hari (pada ' . $batch->tanggal_kadaluarsa->format('d-m-Y') . '). Terdapat ' . $batch->sisa_stok . ' ' . $bahanBaku->satuan . ' stok yang akan kadaluarsa di batch ini.'
                    );
                    // Pertimbangkan untuk menambahkan flag di tabel batch_bahan_baku (misal: notifikasi_hampir_kadaluarsa_terkirim)
                    // agar notifikasi ini hanya terkirim sekali per batch yang hampir kadaluarsa.
                } else {
                    Log::info("Kondisi: Batch Aman (belum mendekati kadaluarsa atau stok habis).");
                }
            } else {
                Log::info("  - Batch ID: {$batch->id} tidak memiliki tanggal kadaluarsa.");
            }
        }
        Log::info("--- Selesai checkAndNotify untuk BahanBaku: {$bahanBaku->nama_bahan} ---");
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
     *
     * @param BahanBaku $bahanBaku Objek BahanBaku yang terkait dengan notifikasi.
     * @param string $subject Subjek notifikasi.
     * @param string $message Isi notifikasi.
     * @return void
     */
    private static function sendNotification(BahanBaku $bahanBaku, string $subject, string $message): void
    {
        Log::warning("[NOTIFIKASI STOK] {$subject}: {$message}");

        $owners = User::where('role', 'owner')->get();

        if ($owners->isNotEmpty()) {
            foreach ($owners as $owner) {
                // 1. Kirim Email
                if ($owner->email) {
                    try {
                        // PENTING: Pastikan Mailable LowStockNotification Anda menggunakan parameter $message ini
                        // di dalam view email-nya. Contoh: $this->messageContent di view Blade.
                        Mail::to($owner->email)->send(new LowStockNotification($bahanBaku, $subject, $message));
                        Log::info("Notifikasi email untuk '{$subject}' berhasil dikirim ke {$owner->email}.");
                    } catch (\Exception $e) {
                        Log::error("Gagal mengirim email notifikasi '{$subject}' ke {$owner->email}: " . $e->getMessage());
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
