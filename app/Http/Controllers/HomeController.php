<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\BatchBahanBaku;
use App\Models\BahanBakuKeluar;
use App\Models\DetailTransaksi;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class HomeController extends Controller
{
    // Ganti seluruh method dashboard Anda dengan yang ini
    public function dashboard(Request $request)
    {
        $periode = $request->input('periode', 'yearly'); // Default ke tahunan

        $labels = [];
        $dataPenjualan = [];
        $dataBahanMasuk = [];
        $dataBahanKeluar = [];

        // ==========================================================
        // ===== LOGIKA BARU UNTUK TAMPILAN HARIAN =====
        // ==========================================================
        if ($periode == 'daily') {
            $today = Carbon::today();
            
            // Query data untuk hari ini, dikelompokkan per jam
            $penjualan = Transaksi::whereDate('created_at', $today)
                ->select(DB::raw('HOUR(created_at) as hour_group'), DB::raw('SUM(total_harga) as total'))
                ->groupBy('hour_group')->get()->keyBy('hour_group');

            $bahanMasuk = BatchBahanBaku::whereDate('created_at', $today)
                ->select(DB::raw('HOUR(created_at) as hour_group'), DB::raw('SUM(jumlah_awal) as total'))
                ->groupBy('hour_group')->get()->keyBy('hour_group');

            $bahanKeluar = BahanBakuKeluar::whereDate('created_at', $today)
                ->select(DB::raw('HOUR(created_at) as hour_group'), DB::raw('SUM(jumlah_keluar) as total'))
                ->groupBy('hour_group')->get()->keyBy('hour_group');

            // Siapkan label dan data untuk 24 jam
            for ($hour = 0; $hour < 24; $hour++) {
                $labels[] = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00'; // Format jam: 00:00, 01:00, dst.
                $dataPenjualan[] = $penjualan[$hour]->total ?? 0;
                $dataBahanMasuk[] = $bahanMasuk[$hour]->total ?? 0;
                $dataBahanKeluar[] = $bahanKeluar[$hour]->total ?? 0;
            }
        
        // ======================================================================
        // ===== KODE LAMA ANDA UNTUK MINGGUAN/BULANAN/TAHUNAN (TETAP DIPAKAI) =====
        // ======================================================================
        } else {
            $endDate = Carbon::now()->endOfDay();
            
            if ($periode == 'yearly') {
                $startDate = Carbon::now()->subMonths(11)->startOfMonth();
                $labelFormat = 'M Y'; // Format label: Jun 2024, Jul 2024
                $dbFormat = '%Y-%m';
                $period = CarbonPeriod::create($startDate, '1 month', $endDate);
            } else { // Handles 'monthly' and 'weekly'
                $days = ($periode == 'monthly') ? 30 : 7;
                $startDate = Carbon::now()->subDays($days - 1)->startOfDay();
                $labelFormat = 'd M'; // Format label: 14 Jun, 15 Jun
                $dbFormat = '%Y-%m-%d';
                $period = CarbonPeriod::create($startDate, '1 day', $endDate);
            }

            $penjualan = Transaksi::withTrashed()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select(DB::raw("DATE_FORMAT(created_at, '$dbFormat') as date_group"), DB::raw('SUM(total_harga) as total'))
                ->groupBy('date_group')->orderBy('date_group', 'asc')->get()->keyBy('date_group');

            $bahanMasuk = BatchBahanBaku::whereBetween('created_at', [$startDate, $endDate])
                ->select(DB::raw("DATE_FORMAT(created_at, '$dbFormat') as date_group"), DB::raw('SUM(jumlah_awal) as total'))
                ->groupBy('date_group')->get()->keyBy('date_group');

            $bahanKeluar = BahanBakuKeluar::whereBetween('created_at', [$startDate, $endDate])
                ->select(DB::raw("DATE_FORMAT(created_at, '$dbFormat') as date_group"), DB::raw('SUM(jumlah_keluar) as total'))
                ->groupBy('date_group')->get()->keyBy('date_group');
            
            foreach ($period as $date) {
                $labels[] = $date->format($labelFormat);
                $dateKey = $date->format(str_replace(['%Y', '%m', '%d'], ['Y', 'm', 'd'], $dbFormat));
                $dataPenjualan[] = $penjualan[$dateKey]->total ?? 0;
                $dataBahanMasuk[] = $bahanMasuk[$dateKey]->total ?? 0;
                $dataBahanKeluar[] = $bahanKeluar[$dateKey]->total ?? 0;
            }
        }

        // Ambil data untuk Info Box (tidak berubah)
        $lowStockItems = BahanBaku::whereRaw('stok <= batas_minimum AND stok > 0')->get();
        // ===== KODE UNTUK MENGHITUNG ITEM KADALUARSA =====
        $today = Carbon::today();
        $expiryLimitDate = Carbon::today()->addDays(15); // Batas "hampir kadaluarsa" adalah 30 hari dari sekarang

        // Hitung jumlah item unik yang memiliki batch kadaluarsa (dengan sisa stok > 0)
        $expiredItemsCount = BatchBahanBaku::where('sisa_stok', '>', 0)
            ->whereDate('tanggal_kadaluarsa', '<', $today)
            ->distinct('bahan_baku_id')
            ->count('bahan_baku_id');

        // Hitung jumlah item unik yang memiliki batch hampir kadaluarsa
        $expiringSoonItemsCount = BatchBahanBaku::where('sisa_stok', '>', 0)
            ->whereBetween('tanggal_kadaluarsa', [$today, $expiryLimitDate])
            ->distinct('bahan_baku_id')
            ->count('bahan_baku_id');
        // Untuk best selling, kita tetap ambil berdasarkan periode yang dipilih
        $startRange = ($periode == 'daily') ? Carbon::today()->startOfDay() : $startDate;
        $endRange = ($periode == 'daily') ? Carbon::today()->endOfDay() : $endDate;

        $bestSellingMenu = DetailTransaksi::select('menu_id', DB::raw('SUM(jumlah) as total_terjual'))
        ->whereNotNull('menu_id') // <--- TAMBAHKAN BARIS INI
        ->whereHas('transaksi', function ($query) use ($startRange, $endRange) {
            $query->whereBetween('created_at', [$startRange, $endRange]);
        })
        ->groupBy('menu_id')
        ->orderByDesc('total_terjual')
        ->with('menu')
        ->first();

        return view('dashboard', [
            'lowStockItems' => $lowStockItems,
            'expiredItemsCount' => $expiredItemsCount,
            'expiringSoonItemsCount' => $expiringSoonItemsCount,
            'bestSellingMenu' => $bestSellingMenu,
            'labels' => $labels,
            'dataPenjualan' => $dataPenjualan,
            'dataBahanMasuk' => $dataBahanMasuk,
            'dataBahanKeluar' => $dataBahanKeluar,
            'periode' => $periode,
        ]);
    }
}