<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Menambahkan kolom invoice_number setelah kolom id
            $table->string('invoice_number')->after('id')->nullable();
            
            // Opsional: Karena sistem master-detail, kita sebenarnya sudah tidak butuh 
            // kolom product_id dan qty di tabel ini (karena sudah pindah ke rincian). 
            // Tapi untuk saat ini kita biarkan saja agar aman.
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('invoice_number');
        });
    }
};