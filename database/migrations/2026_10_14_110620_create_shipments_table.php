<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('no_nota')->unique();
            $table->date('tanggal');
            $table->string('pks_tujuan');
            $table->string('nopol');
            $table->string('driver');
            $table->decimal('total_kg', 12, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index('tanggal');
            $table->index('pks_tujuan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};