<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // 1. Hapus relasi Foreign Key (kunci tamu) terlebih dahulu agar tidak error saat dihapus
            $table->dropForeign(['product_id']);
            
            // 2. Hapus kolom product_id dan qty karena sekarang sudah pindah ke transaction_details
            $table->dropColumn(['product_id', 'qty']);
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Jika di-rollback, kembalikan kolomnya
            $table->foreignId('product_id')->nullable()->constrained();
            $table->integer('qty')->nullable();
        });
    }
};