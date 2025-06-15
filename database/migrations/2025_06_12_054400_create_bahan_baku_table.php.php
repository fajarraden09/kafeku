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
        Schema::create('bahan_baku', function (Blueprint $table) {
            $table->id(); // PK
            $table->string('nama_bahan');
            $table->integer('stok')->default(0); // <-- TAMBAHKAN BARIS INI
            $table->string('satuan', 50);
            $table->integer('batas_minimum')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bahan_baku'); // Sebaiknya lengkapi juga fungsi down()
    }
};