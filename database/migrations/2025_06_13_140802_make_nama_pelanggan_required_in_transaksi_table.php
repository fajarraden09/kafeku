<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            // Mengubah kolom yang sudah ada menjadi tidak nullable
            // nullable(false) berarti WAJIB DIISI
            $table->string('nama_pelanggan')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            // Logika untuk membatalkan (mengembalikan ke nullable)
            $table->string('nama_pelanggan')->nullable()->change();
        });
    }
};