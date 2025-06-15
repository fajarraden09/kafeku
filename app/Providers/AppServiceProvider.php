<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View; // <-- 1. TAMBAHKAN INI
use App\Http\View\Composers\UnpaidOrdersComposer; // <-- 2. TAMBAHKAN INI

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 3. DAFTARKAN COMPOSER DI SINI
        // Kode ini berarti: "Setiap kali view 'layout.main' di-render,
        // jalankan UnpaidOrdersComposer".
        View::composer('layout.main', UnpaidOrdersComposer::class);
    }
}