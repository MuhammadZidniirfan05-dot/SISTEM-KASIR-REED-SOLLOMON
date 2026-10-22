<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan Migrasi (Menambah Kolom)
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // 1. Tambah kolom barcode asli P(x) setelah kolom image
            $table->string('barcode_original')->nullable()->after('image');
            
            // 2. Tambah kolom barcode terproteksi C(x) setelah barcode_original
            // Gunakan text karena hasil RS bisa cukup panjang
            $table->text('barcode_encoded')->nullable()->after('barcode_original');
        });
    }

    /**
     * Batalkan Migrasi (Menghapus Kolom)
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Menghapus kembali kolom jika terjadi rollback
            $table->dropColumn(['barcode_original', 'barcode_encoded']);
        });
    }
};