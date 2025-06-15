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
        // Menggunakan Schema::table untuk mengubah tabel yang sudah ada
        Schema::table('users', function (Blueprint $table) {
            // Menambahkan kolom 'role' setelah kolom 'email'
            // dengan nilai default 'pengguna'
            $table->string('role')->after('email')->default('pengguna');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Logika untuk membatalkan migrasi (jika diperlukan)
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};