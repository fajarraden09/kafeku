<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('log_konsumsi_batch', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detail_transaksi_id')->constrained('detail_transaksi')->onDelete('cascade');
            $table->foreignId('batch_bahan_baku_id')->constrained('batch_bahan_baku')->onDelete('cascade');
            $table->decimal('jumlah_diambil', 8, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_konsumsi_batches');
    }
};
