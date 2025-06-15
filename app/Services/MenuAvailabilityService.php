<?php

namespace App\Services;

use App\Models\Menu;
use Illuminate\Support\Facades\Log; // <-- TAMBAHKAN INI

class MenuAvailabilityService
{
    public static function update(Menu $menu)
    {
        Log::info('--- Service Dimulai untuk: ' . $menu->nama_menu . ' ---');
        $menu->load('resep.bahanBaku');

        if ($menu->resep->isEmpty()) {
            Log::warning('Menu "' . $menu->nama_menu . '" tidak punya resep. Ketersediaan di-set ke false.');
            $menu->ketersediaan = false;
            $menu->save();
            return;
        }

        $isAvailable = true;

        foreach ($menu->resep as $itemResep) {
            if (!$itemResep->bahanBaku) continue;

            $logMessage = '   - Cek Bahan: ' . $itemResep->bahanBaku->nama_bahan . 
                          ' | Stok: ' . $itemResep->bahanBaku->stok . 
                          ' | Butuh: ' . $itemResep->jumlah_dibutuhkan;

            if ($itemResep->jumlah_dibutuhkan > 0) {
                if ($itemResep->bahanBaku->stok < $itemResep->jumlah_dibutuhkan) {
                    $logMessage .= ' | HASIL: TIDAK CUKUP';
                    Log::info($logMessage);
                    $isAvailable = false;
                    break;
                } else {
                    $logMessage .= ' | HASIL: CUKUP';
                    Log::info($logMessage);
                }
            }
        }

        $menu->ketersediaan = $isAvailable;
        $menu->save();
        Log::info('--- Service Selesai. Ketersediaan menu "' . $menu->nama_menu . '" di-set ke: ' . ($isAvailable ? 'TRUE (1)' : 'FALSE (0)'));
    }
}