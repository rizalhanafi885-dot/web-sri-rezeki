<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->boolean('is_paid')->default(false);
            $table->date('paid_at')->nullable();
        });

        DB::table('shipments')->where('is_paid', false)->update(['cash_account_id' => null]);
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn(['is_paid', 'paid_at']);
        });
    }
};
