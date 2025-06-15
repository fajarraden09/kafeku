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
        Schema::create('bahan_baku_keluar', function (Blueprint $table) {
            $table->id(); // PK
            $table->foreignId('bahan_baku_id')->constrained('bahan_baku');
            $table->foreignId('user_id')->constrained('users');
            // FK ke batch_bahan_baku, bisa null jika tidak diketahui dari batch mana
            $table->foreignId('batch_id')->nullable()->constrained('batch_bahan_baku');
            $table->decimal('jumlah_keluar', 10, 2);
            $table->text('keterangan');
            $table->timestamp('tanggal_keluar')->useCurrent();
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
