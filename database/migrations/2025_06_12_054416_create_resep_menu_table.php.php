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
        Schema::create('resep_menu', function (Blueprint $table) {
            $table->id(); // PK
            // Foreign Key (FK) ke tabel menus
            $table->foreignId('menu_id')->constrained('menus')->onDelete('cascade');
            // Foreign Key (FK) ke tabel bahan_baku
            $table->foreignId('bahan_baku_id')->constrained('bahan_baku')->onDelete('cascade');
            $table->decimal('jumlah_dibutuhkan', 10, 2);
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
