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
        Schema::table('detail_transaksi', function (Blueprint $table) {
            // 1. Hapus foreign key yang lama terlebih dahulu
            // Nama constraint default biasanya: nama_tabel_nama_kolom_foreign
            $table->dropForeign('detail_transaksi_menu_id_foreign');

            // 2. Ubah kolom menu_id agar bisa NULL (nullable)
            $table->unsignedBigInteger('menu_id')->nullable()->change();

            // 3. Buat kembali foreign key dengan aturan ON DELETE SET NULL
            $table->foreign('menu_id')
                ->references('id')
                ->on('menus')
                ->onDelete('set null'); // Aturan baru
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_transaksi', function (Blueprint $table) {
            // Logika untuk membatalkan perubahan jika diperlukan (rollback)
            $table->dropForeign('detail_transaksi_menu_id_foreign');
            
            // Kembalikan kolom agar tidak bisa null
            $table->unsignedBigInteger('menu_id')->nullable(false)->change();

            $table->foreign('menu_id')
                    ->references('id')
                    ->on('menus'); // Kembali ke aturan awal (tanpa onDelete)
        });
    }
};