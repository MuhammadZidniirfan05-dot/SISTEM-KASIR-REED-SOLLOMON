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
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel transactions (Tabel Induk)
            // constrained() otomatis mencari tabel 'transactions'
            // onDelete('cascade') artinya jika transaksi dihapus, rinciannya ikut terhapus
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');

            // Relasi ke tabel products
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            // Data transaksi
            $table->integer('qty');       // Jumlah barang yang dibeli
            $table->bigInteger('price'); // Harga satuan produk saat itu (penting jika harga produk berubah nanti)
            $table->bigInteger('subtotal'); // Hasil dari qty * price
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_details');
    }
};