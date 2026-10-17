<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_inflows', function (Blueprint $table) {
            $table->id();
            $table->string('no_transaksi')->unique();
            $table->date('tanggal');
            $table->string('sumber');
            $table->decimal('nominal', 14, 2);
            $table->text('keterangan');
            $table->timestamps();

            $table->index('tanggal');
            $table->index('sumber');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_inflows');
    }
};
