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
        Schema::create('receivable_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receivable_id')->constrained()->cascadeOnDelete();
            $table->string('no_pembayaran')->unique();
            $table->date('tanggal');
            $table->string('jenis', 20);
            $table->decimal('nominal', 14, 2);
            $table->string('metode', 50)->nullable();
            $table->string('admin_penerima');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['receivable_id', 'tanggal']);
            $table->index('jenis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receivable_transactions');
    }
};
