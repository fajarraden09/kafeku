<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftDeletesToBahanBakuTable extends Migration
{
    public function up()
    {
        Schema::table('bahan_baku', function (Blueprint $table) {
            $table->softDeletes(); // Perintah ini yang akan menambahkan kolom 'deleted_at'
        });
    }

    public function down()
    {
        Schema::table('bahan_baku', function (Blueprint $table) {
            $table->dropSoftDeletes(); // Perintah untuk membatalkan jika migrasi di-rollback
        });
    }
}