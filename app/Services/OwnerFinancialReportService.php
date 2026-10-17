<?php

namespace App\Services;

use App\Models\CashAccount;
use App\Models\CashInflow;
use App\Models\CashOutflow;
use App\Models\CashTransfer;
use App\Models\Purchase;
use App\Models\Receivable;
use App\Models\ReceivableTransaction;
use App\Models\Shipment;
use App\Models\StockOpname;

class OwnerFinancialReportService
{
    public function __construct(private InventoryValuationService $valuationService)
    {
    }

    public function generate(
        string $from,
        string $to,
        CashAccountBalanceService $balanceService,
        ?int $ownerId = null
    ): array
    {
        $shipments = Shipment::query()
            ->whereDate('tanggal', '>=', $from)
            ->whereDate('tanggal', '<=', $to)
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();
        $purchases = Purchase::query()
            ->whereDate('tanggal', '>=', $from)
            ->whereDate('tanggal', '<=', $to)
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();
        $classifications = array_merge(
            CashOutflow::EXPENSE_CATEGORIES,
            CashOutflow::LEGACY_EXPENSE_CLASSIFICATIONS
        );
        $expenses = CashOutflow::query()
            ->with('cashAccount:id,name')
            ->whereDate('tanggal', '>=', $from)
            ->whereDate('tanggal', '<=', $to)
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();
        $inflows = CashInflow::query()
            ->with('cashAccount:id,name')
            ->when($ownerId !== null, function ($query) use ($ownerId) {
                $query->whereHas('cashAccount', fn ($query) => $query->where('owner_id', $ownerId));
            })
            ->when($ownerId === null, function ($query) {
                $query->where(function ($query) {
                    $query->whereNull('cash_account_id')
                        ->orWhereHas('cashAccount', fn ($query) => $query->whereNull('owner_id'));
                });
            })
            ->whereDate('tanggal', '>=', $from)
            ->whereDate('tanggal', '<=', $to)
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();
        $payments = ReceivableTransaction::query()
            ->with('receivable:id,pelanggan,no_transaksi')
            ->where('jenis', ReceivableTransaction::TYPE_PAYMENT)
            ->whereDate('tanggal', '>=', $from)
            ->whereDate('tanggal', '<=', $to)
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();
        $transfers = CashTransfer::query()
            ->with(['sourceAccount:id,name', 'destinationAccount:id,name'])
            ->whereDate('tanggal', '>=', $from)
            ->whereDate('tanggal', '<=', $to)
            ->when($ownerId !== null, function ($query) use ($ownerId) {
                $query->where(function ($query) use ($ownerId) {
                    $query->whereHas('sourceAccount', fn ($query) => $query->where('owner_id', $ownerId))
                        ->orWhereHas('destinationAccount', fn ($query) => $query->where('owner_id', $ownerId));
                });
            })
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        $directCategories = array_keys(array_filter(
            $classifications,
            fn (string $classification) => $classification === 'direct'
        ));
        $operationalCategories = array_keys(array_filter(
            $classifications,
            fn (string $classification) => $classification === 'operational'
        ));
        $costOfGoods = (float) $purchases->sum('total_pembelian');
        $additionalDirectExpenses = (float) $expenses
            ->whereIn('kategori', $directCategories)
            ->sum('nominal');
        $operationalExpenses = (float) $expenses
            ->whereIn('kategori', $operationalCategories)
            ->sum('nominal');
        $revenue = (float) $shipments->sum('total_pendapatan');
        $directExpenses = $costOfGoods + $additionalDirectExpenses;

        $rows = collect()
            ->concat($shipments->map(fn (Shipment $shipment) => $this->row(
                $shipment->tanggal->toDateString(),
                $shipment->no_nota,
                'Penjualan PKS',
                $shipment->pks_tujuan,
                number_format((float) $shipment->total_kg, 2, ',', '.').' Kg x Rp '
                    .number_format((float) $shipment->harga_jual_per_kg, 0, ',', '.'),
                (float) $shipment->total_pendapatan,
                'Pendapatan',
                $shipment->is_paid ? 'Lunas' : 'Belum dibayar'
            )))
            ->concat($purchases->map(fn (Purchase $purchase) => $this->row(
                $purchase->tanggal->toDateString(),
                $purchase->no_nota,
                'Pembayaran Pembelian',
                $purchase->kategori,
                'Petani: '.$purchase->petani,
                (float) $purchase->total_bayar,
                'Pengeluaran',
                'Dibayar'
            )))
            ->concat($expenses->map(fn (CashOutflow $expense) => $this->row(
                $expense->tanggal->toDateString(),
                $expense->no_transaksi,
                ($classifications[$expense->kategori] ?? 'operational') === 'direct'
                    ? 'Beban Langsung'
                    : 'Biaya Operasional',
                $expense->kategori,
                (string) ($expense->keterangan ?: '-'),
                (float) $expense->nominal,
                'Pengeluaran',
                $expense->cashAccount?->name ?? '-'
            )))
            ->concat($inflows->map(fn (CashInflow $inflow) => $this->row(
                $inflow->tanggal->toDateString(),
                $inflow->no_transaksi,
                'Kas Masuk',
                $inflow->sumber,
                (string) ($inflow->keterangan ?: '-'),
                (float) $inflow->nominal,
                'Penerimaan',
                $inflow->cashAccount?->name ?? '-'
            )))
            ->concat($payments->map(fn (ReceivableTransaction $payment) => $this->row(
                $payment->tanggal->toDateString(),
                $payment->no_pembayaran,
                'Pembayaran Piutang',
                $payment->receivable?->pelanggan ?? '-',
                'Transaksi '.$payment->receivable?->no_transaksi,
                (float) $payment->nominal,
                'Penerimaan',
                $payment->cashAccount?->name ?? '-'
            )))
            ->concat($transfers->map(fn (CashTransfer $transfer) => $this->row(
                $transfer->tanggal->toDateString(),
                $transfer->no_transaksi,
                'Transfer Antar Akun',
                ($transfer->sourceAccount?->name ?? '-').' ke '.($transfer->destinationAccount?->name ?? '-'),
                (string) ($transfer->keterangan ?: '-'),
                (float) $transfer->nominal,
                'Transfer',
                'Bukan pendapatan atau beban'
            )))
            ->sortBy(fn (array $row) => [$row['date'], $row['number']])
            ->values();

        return [
            'from' => $from,
            'to' => $to,
            'summary' => [
                'revenue' => $revenue,
                'cost_of_goods' => $costOfGoods,
                'additional_direct_expenses' => $additionalDirectExpenses,
                'direct_expenses' => $directExpenses,
                'operational_expenses' => $operationalExpenses,
                'total_expenses' => $directExpenses + $operationalExpenses,
                'net_profit' => $revenue - $directExpenses - $operationalExpenses,
                'transactions' => $rows->count(),
            ],
            'rows' => $rows,
            'balance_sheet' => $this->balanceSheetAt($to, $balanceService, $ownerId),
        ];
    }

    private function row(
        string $date,
        string $number,
        string $type,
        string $category,
        string $description,
        float $amount,
        string $flow,
        string $status
    ): array {
        return compact('date', 'number', 'type', 'category', 'description', 'amount', 'flow', 'status');
    }

    private function balanceSheetAt(
        string $date,
        CashAccountBalanceService $balanceService,
        ?int $ownerId
    ): array
    {
        $balances = $balanceService->balances($date, $ownerId);
        $cash = array_sum($balances);

        $purchases = Purchase::query()
            ->whereDate('tanggal', '<=', $date)
            ->get(['kategori', 'netto_1', 'netto_2', 'harga_per_kg'])
            ->groupBy('kategori');
        $shipments = Shipment::query()
            ->whereDate('tanggal', '<=', $date)
            ->get(array_merge(['tanggal'], array_values(Shipment::CARGO_FIELDS)));
        $adjustments = StockOpname::query()
            ->whereDate('tanggal', '<=', $date)
            ->get(['kategori', 'selisih_kg'])
            ->groupBy('kategori');

        $inventory = 0.0;
        foreach (Shipment::CARGO_FIELDS as $category => $field) {
            $categoryPurchases = $purchases->get($category, collect());
            $purchasedWeight = (float) $categoryPurchases->sum('netto_1');
            $stock = $purchasedWeight
                - (float) $shipments->sum($field)
                + (float) $adjustments->get($category, collect())->sum('selisih_kg');
            $purchaseCost = (float) $categoryPurchases->sum(
                fn (Purchase $purchase) => (float) $purchase->netto_2 * (float) $purchase->harga_per_kg
            );
            $inventory += $this->valuationService->remainingValue($stock, $purchasedWeight, $purchaseCost);
        }

        $baseReceivables = (float) Receivable::query()
            ->whereDate('tanggal', '<=', $date)
            ->sum('nominal_awal');
        $loans = (float) ReceivableTransaction::query()
            ->where('jenis', ReceivableTransaction::TYPE_LOAN)
            ->whereDate('tanggal', '<=', $date)
            ->sum('nominal');
        $payments = (float) ReceivableTransaction::query()
            ->where('jenis', ReceivableTransaction::TYPE_PAYMENT)
            ->whereDate('tanggal', '<=', $date)
            ->sum('nominal');
        $unpaidSales = (float) Shipment::query()
            ->whereDate('tanggal', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->where('is_paid', false)
                    ->orWhere(function ($query) use ($date) {
                        $query->where('is_paid', true)
                            ->whereDate('paid_at', '>', $date);
                    });
            })
            ->sum('total_pendapatan');
        $receivables = max(0, $baseReceivables + $loans - $payments + $unpaidSales);
        $totalAssets = $cash + $inventory + $receivables;

        return [
            'date' => $date,
            'cash' => $cash,
            'inventory' => $inventory,
            'receivables' => $receivables,
            'liabilities' => 0,
            'total_assets' => $totalAssets,
            'equity' => $totalAssets,
        ];
    }
}
