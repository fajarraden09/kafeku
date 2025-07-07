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
        // Pastikan tabel ada sebelum dihapus untuk menghindari error
        if (Schema::hasTable('batches')) {
            Schema::dropIfExists('batches');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Opsional: Jika Anda ingin bisa mengembalikan tabel ini,
        // Anda bisa mendefinisikan ulang strukturnya di sini.
        // Namun, jika Anda yakin tidak dibutuhkan, bisa dikosongkan.
        // Contoh struktur dasar jika ingin bisa di-rollback:
        // Schema::create('batches', function (Blueprint $table) {
        //     $table->id();
        //     $table->unsignedBigInteger('bahan_baku_id');
        //     $table->integer('jumlah_awal');
        //     $table->integer('sisa_stok');
        //     $table->date('tanggal_kadaluarsa')->nullable();
        //     $table->timestamps();
        // });
    }
};