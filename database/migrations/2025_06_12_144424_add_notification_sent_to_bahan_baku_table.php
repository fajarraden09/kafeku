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
        Schema::table('bahan_baku', function (Blueprint $table) {
            // default(false) berarti notifikasi belum pernah terkirim
            $table->boolean('notifikasi_terkirim')->default(false)->after('batas_minimum');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bahan_baku', function (Blueprint $table) {
            $table->dropColumn('notifikasi_terkirim'); // Tambahkan ini
        });
    }
};
