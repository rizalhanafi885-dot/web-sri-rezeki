<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_accounts', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->foreignId('owner_id')->nullable()->after('id')->constrained('users')->restrictOnDelete();
            $table->unique(['owner_id', 'name']);
        });

        $hasRamAccount = DB::table('cash_accounts')
            ->whereNull('owner_id')
            ->whereRaw('LOWER(name) = ?', ['kas ram'])
            ->exists();

        if (! $hasRamAccount) {
            DB::table('cash_accounts')->insert([
                'name' => 'KAS RAM',
                'type' => 'cash',
                'initial_balance' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('cash_accounts', function (Blueprint $table) {
            $table->dropUnique(['owner_id', 'name']);
            $table->dropConstrainedForeignId('owner_id');
            $table->unique('name');
        });
    }
};
