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
        Schema::create('batch_bahan_baku', function (Blueprint $table) {
            $table->id(); // PK
            $table->foreignId('bahan_baku_id')->constrained('bahan_baku')->onDelete('cascade');
            $table->string('kode_batch')->unique()->nullable();
            $table->decimal('jumlah_awal', 10, 2);
            $table->decimal('sisa_stok', 10, 2);
            $table->timestamp('tanggal_masuk')->useCurrent();
            $table->date('tanggal_kadaluarsa')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
