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
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('kategori', 100);
            $table->decimal('stok_sistem_kg', 12, 2);
            $table->decimal('stok_fisik_kg', 12, 2);
            $table->decimal('selisih_kg', 12, 2);
            $table->string('petugas', 255);
            $table->timestamps();

            $table->index(['kategori', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opnames');
    }
};
