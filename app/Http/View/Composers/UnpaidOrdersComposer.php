<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use App\Models\Transaksi;

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
        // Hitung jumlah transaksi yang statusnya 'Belum Dibayar'
        $unpaidOrdersCount = Transaksi::where('status_pembayaran', 'Belum Dibayar')->count();

        // Bagikan variabel 'unpaidOrdersCount' ke view yang dituju
        $view->with('unpaidOrdersCount', $unpaidOrdersCount);
    }
}