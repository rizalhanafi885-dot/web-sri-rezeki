<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->decimal('tbs_kg', 12, 2)->default(0);
            $table->decimal('brondolan_kg', 12, 2)->default(0);
            $table->decimal('brd_afkir_kg', 12, 2)->default(0);
            $table->decimal('buah_kecil_kg', 12, 2)->default(0);
        });

        DB::table('shipments')->update([
            'tbs_kg' => DB::raw('total_kg'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn([
                'tbs_kg',
                'brondolan_kg',
                'brd_afkir_kg',
                'buah_kecil_kg',
            ]);
        });
    }
};
