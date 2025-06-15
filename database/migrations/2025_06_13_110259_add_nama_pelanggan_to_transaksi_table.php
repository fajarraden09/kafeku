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
        Schema::table('transaksi', function (Blueprint $table) {
            // Dibuat nullable karena mungkin ada transaksi cepat yang tidak perlu nama
            $table->string('nama_pelanggan')->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropColumn('nama_pelanggan');
        });
    }
};
