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
        // Modifikasi tabel transaksi
        Schema::table('transaksi', function (Blueprint $table) {
            // Hapus foreign key yang lama terlebih dahulu
            $table->dropForeign(['user_id']);

            // Ubah kolom user_id agar bisa nullable (ini WAJIB untuk set null)
            // dan tambahkan kembali foreign key dengan aturan baru
            $table->foreignId('user_id')->nullable()->change()->constrained('users')->onDelete('set null');
        });

        // Modifikasi tabel bahan_baku_keluar
        Schema::table('bahan_baku_keluar', function (Blueprint $table) {
            // Hapus foreign key yang lama
            $table->dropForeign(['user_id']);

            // Tambahkan lagi dengan aturan baru
            $table->foreignId('user_id')->nullable()->change()->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Logika untuk membatalkan perubahan jika diperlukan
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreignId('user_id')->change()->constrained('users');
        });

        Schema::table('bahan_baku_keluar', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreignId('user_id')->change()->constrained('users');
        });
    }
};