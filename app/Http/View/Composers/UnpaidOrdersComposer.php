<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Schema; // <-- 1. IMPORT KELAS SCHEMA
use Illuminate\Support\Facades\Log;      // <-- 2. IMPORT KELAS LOG (untuk debugging)

class UnpaidOrdersComposer
{
    /**
     * Mengikat data ke view.
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $unpaidOrdersCount = 0; // Atur nilai default menjadi 0

        try {
            // 3. Cek terlebih dahulu apakah tabel 'transaksi' sudah ada di database
            if (Schema::hasTable('transaksi')) {
                // Jika sudah ada, baru jalankan query untuk menghitung
                $unpaidOrdersCount = Transaksi::where('status_pembayaran', 'Belum Dibayar')->count();
            }
        } catch (\Exception $e) {
            // 4. Jika terjadi error apapun (misalnya saat migrasi sedang berjalan),
            //    catat error di log dan tetap lanjutkan dengan nilai 0 agar aplikasi tidak crash.
            Log::error('Error in UnpaidOrdersComposer: ' . $e->getMessage());
            $unpaidOrdersCount = 0;
        }
        
        // Bagikan variabel ke view dengan aman
        $view->with('unpaidOrdersCount', $unpaidOrdersCount);
    }
}