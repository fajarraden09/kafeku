<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\User;
use App\Mail\LowStockNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StockNotificationService
{
    /**
     * Memeriksa stok dan mengirim notifikasi jika perlu.
     */
    public static function checkAndNotify(BahanBaku $bahanBaku)
    {
        // Kirim notifikasi HANYA JIKA stok di bawah minimum DAN notifikasi belum pernah terkirim
        if ($bahanBaku->stok <= $bahanBaku->batas_minimum && !$bahanBaku->notifikasi_terkirim) {

            $owner = User::where('role', 'owner')->first();

            if ($owner) {
                $message = "Peringatan Stok Rendah!\n\nNama Bahan: {$bahanBaku->nama_bahan}\nSisa Stok: {$bahanBaku->stok} {$bahanBaku->satuan}\n\nMohon segera lakukan pemesanan ulang.";

                // 1. Kirim Email
                if ($owner->email) {
                    try {
                        Mail::to($owner->email)->send(new LowStockNotification($bahanBaku));
                        Log::info("Notifikasi email untuk {$bahanBaku->nama_bahan} berhasil dikirim ke {$owner->email}.");
                    } catch (\Exception $e) {
                        Log::error("Gagal mengirim email notifikasi: " . $e->getMessage());
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
                        Log::info("Notifikasi WhatsApp untuk {$bahanBaku->nama_bahan} berhasil dikirim.");
                    } catch (\Exception $e) {
                        Log::error("Gagal mengirim notifikasi WhatsApp: " . $e->getMessage());
                    }
                }

                // 3. Tandai bahwa notifikasi sudah terkirim untuk item ini
                $bahanBaku->notifikasi_terkirim = true;
                $bahanBaku->save();
            }
        }
    }

    /**
     * Mereset flag notifikasi saat stok ditambahkan.
     */
    public static function resetNotificationFlag(BahanBaku $bahanBaku)
    {
        if ($bahanBaku->stok > $bahanBaku->batas_minimum) {
            $bahanBaku->notifikasi_terkirim = false;
            $bahanBaku->save();
            Log::info("Flag notifikasi untuk {$bahanBaku->nama_bahan} telah di-reset.");
        }
    }
}