<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Mengubah tabel bahan_baku
        Schema::table('bahan_baku', function (Blueprint $table) {
            // Mengubah tipe data kolom stok dan batas_minimum menjadi DECIMAL
            // Angka 10 adalah total digit, dan 2 adalah jumlah digit di belakang koma.
            $table->decimal('stok', 10, 2)->default(0)->change();
            $table->decimal('batas_minimum', 10, 2)->default(0)->change();
        });

        // Mengubah tabel resep_menu
        // Pastikan Anda sudah punya tabel ini. Jika belum, buat dulu.
        if (Schema::hasTable('resep_menu')) {
            Schema::table('resep_menu', function (Blueprint $table) {
                $table->decimal('jumlah_dibutuhkan', 10, 2)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Perintah untuk membatalkan (mengembalikan ke integer)
        Schema::table('bahan_baku', function (Blueprint $table) {
            $table->integer('stok')->default(0)->change();
            $table->integer('batas_minimum')->default(0)->change();
        });

        if (Schema::hasTable('resep_menu')) {
            Schema::table('resep_menu', function (Blueprint $table) {
                $table->integer('jumlah_dibutuhkan')->change();
            });
        }
    }
};