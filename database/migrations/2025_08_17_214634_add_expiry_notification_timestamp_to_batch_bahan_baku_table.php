<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExpiryNotificationTimestampToBatchBahanBakuTable extends Migration
{
    public function up()
    {
        Schema::table('batch_bahan_baku', function (Blueprint $table) {
            // Kolom ini menyimpan tanggal notifikasi kadaluarsa/hampir kadaluarsa dikirim.
            $table->timestamp('notifikasi_kadaluarsa_terakhir_dikirim_at')->nullable()->after('tanggal_kadaluarsa');
        });
    }

    public function down()
    {
        Schema::table('batch_bahan_baku', function (Blueprint $table) {
            $table->dropColumn('notifikasi_kadaluarsa_terakhir_dikirim_at');
        });
    }
}