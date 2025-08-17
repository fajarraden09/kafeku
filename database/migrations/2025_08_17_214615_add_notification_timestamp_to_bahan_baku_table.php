<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNotificationTimestampToBahanBakuTable extends Migration
{
    public function up()
    {
        Schema::table('bahan_baku', function (Blueprint $table) {
            // Kolom ini akan menyimpan tanggal terakhir notifikasi stok (rendah/habis) dikirim.
            $table->timestamp('notifikasi_stok_terakhir_dikirim_at')->nullable()->after('notifikasi_terkirim');
        });
    }

    public function down()
    {
        Schema::table('bahan_baku', function (Blueprint $table) {
            $table->dropColumn('notifikasi_stok_terakhir_dikirim_at');
        });
    }
}
