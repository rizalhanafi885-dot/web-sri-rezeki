<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('type', 20);
            $table->decimal('initial_balance', 14, 2)->default(0);
            $table->timestamps();
        });

        $cashAccountId = DB::table('cash_accounts')->insertGetId([
            'name' => 'Kas Utama',
            'type' => 'cash',
            'initial_balance' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach (['cash_inflows', 'cash_outflows', 'purchases', 'shipments', 'receivable_transactions'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('cash_account_id')->nullable()->constrained('cash_accounts');
            });

            DB::table($tableName)->whereNull('cash_account_id')->update([
                'cash_account_id' => $cashAccountId,
            ]);
        }

        Schema::create('cash_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('no_transaksi')->unique();
            $table->date('tanggal');
            $table->foreignId('source_account_id')->constrained('cash_accounts');
            $table->foreignId('destination_account_id')->constrained('cash_accounts');
            $table->decimal('nominal', 14, 2);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_transfers');

        foreach (['cash_inflows', 'cash_outflows', 'purchases', 'shipments', 'receivable_transactions'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('cash_account_id');
            });
        }

        Schema::dropIfExists('cash_accounts');
    }
};
