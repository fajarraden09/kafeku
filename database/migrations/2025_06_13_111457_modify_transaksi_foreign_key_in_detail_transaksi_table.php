<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_transaksi', function (Blueprint $table) {
            // Hapus foreign key yang lama
            $table->dropForeign(['transaksi_id']);

            // Tambahkan lagi dengan aturan onDelete('cascade')
            // Artinya, jika transaksi induk dihapus, detail ini akan ikut terhapus.
            $table->foreign('transaksi_id')
                  ->references('id')
                  ->on('transaksi')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // Logika untuk membatalkan jika perlu
        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->dropForeign(['transaksi_id']);
            $table->foreign('transaksi_id')->references('id')->on('transaksi');
        });
    }
};