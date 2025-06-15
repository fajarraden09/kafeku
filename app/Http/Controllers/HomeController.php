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
use Carbon\CarbonPeriod; // <-- 1. PASTIKAN INI DI-IMPORT

class HomeController extends Controller
{
    public function dashboard(Request $request)
    {
        // Tentukan periode waktu berdasarkan input dari tombol
        $periode = $request->input('periode', 'yearly'); // Default ke tahunan agar lebih luas
        $endDate = Carbon::now()->endOfDay();
        
        // ==========================================================
        // ===== AWAL LOGIKA DINAMIS UNTUK PERIODE =====
        // ==========================================================
        
        // Siapkan variabel berdasarkan periode yang dipilih
        if ($periode == 'yearly') {
            // Jika tahunan, kita ambil data 12 bulan terakhir dan kelompokkan per bulan
            $startDate = Carbon::now()->subMonths(11)->startOfMonth();
            $labelFormat = 'M Y'; // Format label: Jun 2024, Jul 2024
            $dbFormat = '%Y-%m'; // Format untuk query group by di MySQL
            $period = CarbonPeriod::create($startDate, '1 month', $endDate);
        } else {
            // Jika mingguan atau bulanan, kita ambil data harian
            $days = ($periode == 'monthly') ? 30 : 7;
            $startDate = Carbon::now()->subDays($days - 1)->startOfDay();
            $labelFormat = 'd M'; // Format label: 14 Jun, 15 Jun
            $dbFormat = '%Y-%m-%d'; // Format untuk query group by di MySQL
            $period = CarbonPeriod::create($startDate, '1 day', $endDate);
        }

        // ==========================================================
        // ===== AKHIR LOGIKA DINAMIS =====
        // ==========================================================

        // Query data penjualan dengan pengelompokan dinamis
        $penjualan = Transaksi::withTrashed()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw("DATE_FORMAT(created_at, '$dbFormat') as date_group"), DB::raw('SUM(total_harga) as total'))
            ->groupBy('date_group')->orderBy('date_group', 'asc')->get()->keyBy('date_group');

        // Query data bahan masuk dengan pengelompokan dinamis
        $bahanMasuk = BatchBahanBaku::whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw("DATE_FORMAT(created_at, '$dbFormat') as date_group"), DB::raw('SUM(jumlah_awal) as total'))
            ->groupBy('date_group')->get()->keyBy('date_group');

        // Query data bahan keluar dengan pengelompokan dinamis
        $bahanKeluar = BahanBakuKeluar::whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw("DATE_FORMAT(created_at, '$dbFormat') as date_group"), DB::raw('SUM(jumlah_keluar) as total'))
            ->groupBy('date_group')->get()->keyBy('date_group');

        // Ambil data untuk Info Box (tidak berubah)
        $lowStockItems = BahanBaku::whereRaw('stok <= batas_minimum AND stok > 0')->get();
        $bestSellingMenu = DetailTransaksi::select('menu_id', DB::raw('SUM(jumlah) as total_terjual'))
            ->whereHas('transaksi', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })->groupBy('menu_id')->orderByDesc('total_terjual')->with('menu')->first();

        // Proses data agar sesuai format Chart.js
        $labels = [];
        $dataPenjualan = [];
        $dataBahanMasuk = [];
        $dataBahanKeluar = [];
        foreach ($period as $date) {
            $labels[] = $date->format($labelFormat);
            $dateKey = $date->format(str_replace(['%Y', '%m', '%d'], ['Y', 'm', 'd'], $dbFormat));
            $dataPenjualan[] = $penjualan[$dateKey]->total ?? 0;
            $dataBahanMasuk[] = $bahanMasuk[$dateKey]->total ?? 0;
            $dataBahanKeluar[] = $bahanKeluar[$dateKey]->total ?? 0;
        }

        return view('dashboard', [
            'lowStockItems' => $lowStockItems,
            'bestSellingMenu' => $bestSellingMenu,
            'labels' => $labels,
            'dataPenjualan' => $dataPenjualan,
            'dataBahanMasuk' => $dataBahanMasuk,
            'dataBahanKeluar' => $dataBahanKeluar,
            'periode' => $periode,
        ]);
    }
}