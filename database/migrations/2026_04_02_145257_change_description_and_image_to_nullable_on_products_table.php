<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
   public function up()
{
    Schema::table('products', function (Blueprint $table) {
        // Mengubah kolom menjadi nullable
        $table->text('description')->nullable()->change();
        $table->string('image')->nullable()->change();
    });
}

public function down()
{
    Schema::table('products', function (Blueprint $table) {
        $table->text('description')->nullable(false)->change();
        $table->string('image')->nullable(false)->change();
    });
}
};
