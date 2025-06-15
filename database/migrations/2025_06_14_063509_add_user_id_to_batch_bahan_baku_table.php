<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('batch_bahan_baku', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('bahan_baku_id')->constrained('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('batch_bahan_baku', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
