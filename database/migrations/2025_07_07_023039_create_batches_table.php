<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_baku_id')->constrained('bahan_baku')->onDelete('cascade');
            $table->integer('jumlah_awal'); // Atau ganti dengan 'sisa_stok_batch' jika Anda melacaknya terpisah
            $table->date('tanggal_kadaluarsa')->nullable(); // nullable jika tidak semua batch punya tgl kadaluarsa
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Opsional, sesuaikan dengan tabel user Anda
            $table->timestamps(); // created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('batches');
    }
}