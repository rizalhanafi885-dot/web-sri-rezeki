<?php

namespace App\Services;

use App\Models\CashAccount;
use App\Models\CashInflow;
use App\Models\CashOutflow;
use App\Models\CashTransfer;
use App\Models\Purchase;
use App\Models\ReceivableTransaction;
use App\Models\Shipment;

class CashAccountBalanceService
{
    public function balances(?string $asOf = null, ?int $ownerId = null): array
    {
        $accountQuery = CashAccount::query();
        $ownerId === null
            ? $accountQuery->whereNull('owner_id')
            : $accountQuery->where('owner_id', $ownerId);

        $balances = $accountQuery
            ->pluck('initial_balance', 'id')
            ->map(fn ($balance) => (float) $balance)
            ->all();

        $this->addGroupedAmounts($balances, CashInflow::query(), 'nominal', 'cash_account_id', $asOf);
        $this->addGroupedAmounts(
            $balances,
            Shipment::query()->where('is_paid', true)->when($asOf, function ($query) use ($asOf) {
                $query->where(function ($query) use ($asOf) {
                    $query->whereDate('paid_at', '<=', $asOf)
                        ->orWhere(function ($query) use ($asOf) {
                            $query->whereNull('paid_at')->whereDate('tanggal', '<=', $asOf);
                        });
                });
            }),
            'total_pendapatan',
            'cash_account_id'
        );
        $this->addGroupedAmounts(
            $balances,
            ReceivableTransaction::query()->where('jenis', ReceivableTransaction::TYPE_PAYMENT),
            'nominal',
            'cash_account_id',
            $asOf
        );
        $this->subtractGroupedAmounts($balances, CashOutflow::query(), 'nominal', 'cash_account_id', $asOf);
        $this->subtractGroupedAmounts($balances, Purchase::query(), 'total_bayar', 'cash_account_id', $asOf);
        $this->addGroupedAmounts($balances, CashTransfer::query(), 'nominal', 'destination_account_id', $asOf);
        $this->subtractGroupedAmounts($balances, CashTransfer::query(), 'nominal', 'source_account_id', $asOf);

        return $balances;
    }

    private function addGroupedAmounts(
        array &$balances,
        $query,
        string $column,
        string $accountColumn = 'cash_account_id',
        ?string $asOf = null
    ): void
    {
        $query->when($asOf, fn ($query) => $query->whereDate('tanggal', '<=', $asOf));
        foreach ($query->selectRaw("{$accountColumn}, SUM({$column}) as total")
            ->groupBy($accountColumn)
            ->get() as $row) {
            if (isset($balances[$row->{$accountColumn}])) {
                $balances[$row->{$accountColumn}] += (float) $row->total;
            }
        }
    }

    private function subtractGroupedAmounts(
        array &$balances,
        $query,
        string $column,
        string $accountColumn = 'cash_account_id',
        ?string $asOf = null
    ): void
    {
        $query->when($asOf, fn ($query) => $query->whereDate('tanggal', '<=', $asOf));
        foreach ($query->selectRaw("{$accountColumn}, SUM({$column}) as total")
            ->groupBy($accountColumn)
            ->get() as $row) {
            if (isset($balances[$row->{$accountColumn}])) {
                $balances[$row->{$accountColumn}] -= (float) $row->total;
            }
        }
    }
}
