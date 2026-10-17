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
        Schema::table('shipments', function (Blueprint $table) {
            $table->decimal('harga_jual_per_kg', 10, 2)->default(0)->after('total_kg');
            $table->decimal('total_pendapatan', 24, 2)->default(0)->after('harga_jual_per_kg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn(['harga_jual_per_kg', 'total_pendapatan']);
        });
    }
};
