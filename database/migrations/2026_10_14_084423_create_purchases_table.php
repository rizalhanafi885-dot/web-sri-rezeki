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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('no_nota')->unique();
            $table->date('tanggal');
            $table->string('petani');
            $table->string('kategori'); // TBS, Brondolan, BRD Afkir, Buah Kecil, dll
            $table->decimal('harga_per_kg', 12, 2);
            $table->decimal('bruto', 10, 2);
            $table->decimal('tara', 10, 2);
            $table->decimal('netto_1', 10, 2);
            $table->decimal('rafaksi', 5, 2)->default(0); // persen potongan
            $table->decimal('netto_2', 10, 2); // dasar bayar
            $table->decimal('upah_bongkar', 12, 2)->default(0);
            $table->decimal('total_pembelian', 14, 2);
            $table->decimal('total_bayar', 14, 2); // dibayar
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
