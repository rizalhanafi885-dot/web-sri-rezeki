<?php

namespace App\Http\Controllers;

use App\Models\CashAccount;
use App\Models\CashInflow;
use App\Models\CashOutflow;
use App\Models\Purchase;
use App\Models\ReceivableTransaction;
use App\Models\Shipment;
use App\Services\CashAccountBalanceService;
use App\Services\OwnerFinancialReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $reportTypes = [
            'all' => 'Semua Laporan',
            'purchases' => 'Laporan Pembelian',
            'shipments' => 'Laporan Pengiriman PKS',
            'cash-flow' => 'Laporan Arus Kas',
            'profit-loss' => 'Laporan Laba Rugi',
        ];
        $hasFilters = $request->query->has('from') || $request->query->has('to') || $request->query->has('type');
        $filters = [];
        $rows = collect();
        $summary = [];
        $sections = [];

        if ($hasFilters) {
            $filters = Validator::make($request->query(), [
                'from' => ['required', 'date'],
                'to' => ['required', 'date', 'after_or_equal:from'],
                'type' => ['required', Rule::in(array_keys($reportTypes))],
            ])->validate();

            if ($filters['type'] === 'all') {
                $sections = $this->reportSections($filters, $reportTypes);
            } else {
                [$rows, $summary] = $this->reportData($filters);
            }
        }

        return view('admin.reports.index', [
            'reportTypes' => $reportTypes,
            'filters' => $filters,
            'hasFilters' => $hasFilters,
            'rows' => $rows,
            'summary' => $summary,
            'sections' => $sections,
        ]);
    }

    public function export(
        Request $request,
        OwnerFinancialReportService $financialReportService,
        CashAccountBalanceService $balanceService
    ) {
        $reportTypes = [
            'all' => 'Semua Laporan',
            'purchases' => 'Laporan Pembelian',
            'shipments' => 'Laporan Pengiriman PKS',
            'cash-flow' => 'Laporan Arus Kas',
            'profit-loss' => 'Laporan Laba Rugi',
        ];
        $filters = Validator::make($request->query(), [
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
            'type' => ['required', Rule::in(array_keys($reportTypes))],
        ])->validate();
        $sections = $this->reportSections($filters, $reportTypes);
        $period = $filters['from'].'-'.$filters['to'];
        $name = $filters['type'] === 'all' ? 'semua-laporan' : $filters['type'];

        $financial = $financialReportService->generate(
            $filters['from'],
            $filters['to'],
            $balanceService
        );
        $accountBalances = $balanceService->balances($filters['to']);
        $cashAccounts = CashAccount::query()
            ->whereNull('owner_id')
            ->orderBy('type')
            ->orderBy('name')
            ->get()
            ->map(fn (CashAccount $account) => [
                'name' => $account->name,
                'type' => $account->type,
                'initial_balance' => (float) $account->initial_balance,
                'balance' => (float) ($accountBalances[$account->id] ?? 0),
            ]);
        $shipments = Shipment::query()
            ->whereDate('tanggal', '>=', $filters['from'])
            ->whereDate('tanggal', '<=', $filters['to'])
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();
        $negativeCashBalances = abs((float) $cashAccounts
            ->sum(fn (array $account) => min(0, $account['balance'])));
        $assetBreakdown = $cashAccounts
            ->map(fn (array $account) => [
                'name' => $account['name'],
                'amount' => max(0, $account['balance']),
            ])
            ->push(['name' => 'Valuasi Stok Barang', 'amount' => $financial['balance_sheet']['inventory']])
            ->push(['name' => 'Piutang Usaha', 'amount' => $financial['balance_sheet']['receivables']])
            ->filter(fn (array $asset) => $asset['amount'] > 0)
            ->values();
        $assetsTotal = (float) $assetBreakdown->sum('amount');
        $equity = $assetsTotal - $negativeCashBalances;
        $chartColors = ['#1686d9', '#49c9d2', '#12bc68', '#7ed321', '#ffc928', '#f87924', '#fa4b52'];
        $chartOffset = 0.0;
        $assetBreakdown = $assetBreakdown->map(function (array $asset, int $index) use (
            $assetsTotal,
            $chartColors,
            &$chartOffset
        ) {
            $percentage = $assetsTotal > 0 ? $asset['amount'] / $assetsTotal * 100 : 0;
            $dashLength = $percentage * 2.5133;
            $asset['percentage'] = $percentage;
            $asset['color'] = $chartColors[$index % count($chartColors)];
            $asset['dash_length'] = $dashLength;
            $asset['dash_offset'] = -$chartOffset;
            $chartOffset += $dashLength;

            return $asset;
        });
        $chartSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">'
            .'<circle cx="50" cy="50" r="40" fill="none" stroke="#e8edf2" stroke-width="18"/>'
            .$assetBreakdown->map(fn (array $asset) => $asset['dash_length'] > 0
                ? '<circle cx="50" cy="50" r="40" fill="none" stroke="'.$asset['color'].'" stroke-width="18" stroke-dasharray="'
                    .number_format($asset['dash_length'], 3, '.', '').' 251.327" stroke-dashoffset="'
                    .number_format($asset['dash_offset'], 3, '.', '').'" transform="rotate(-90 50 50)"/>'
                : '')
                ->implode('')
            .'<circle cx="50" cy="50" r="29" fill="#fff"/></svg>';
        $cashCards = $cashAccounts->take(2)->values();
        if ($cashCards->count() < 2) {
            $cashCards->push([
                'name' => 'Total Kas dan Bank',
                'balance' => (float) $financial['balance_sheet']['cash'],
            ]);
        }
        $revenue = (float) $financial['summary']['revenue'];
        $savingMargin = $revenue > 0
            ? (float) $financial['summary']['net_profit'] / $revenue * 100
            : 0;
        $iconImages = collect([
            'bank' => '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48"><path fill="#f7c948" d="M5 17 24 6l19 11v3H5z"/><path fill="#d8a900" d="M8 22h6v16H8zm13 0h6v16h-6zm13 0h6v16h-6zM5 40h38v4H5z"/><path fill="#fff1ad" d="M24 11 12 18h24z"/></svg>',
            'wallet' => '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48"><rect x="6" y="12" width="35" height="29" rx="5" fill="#416eb5"/><path fill="#7398d3" d="M10 8h27a4 4 0 0 1 4 4v3H10a4 4 0 0 1 0-7"/><path fill="#2d568f" d="M6 18h35v6H6z"/><path fill="#e6efff" d="M30 24h13a3 3 0 0 1 3 3v8a3 3 0 0 1-3 3H30a7 7 0 0 1 0-14"/><circle cx="34" cy="31" r="2" fill="#416eb5"/><path fill="#fff" d="M12 27h10v3H12zm0 6h7v3h-7z"/></svg>',
            'basket' => '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48"><path fill="#10b981" d="m7 19 5 21h25l5-21H7zm7 4h4v13h-4zm8 0h4v13h-4zm8 0h4v13h-4z"/><path fill="none" stroke="#059669" stroke-linecap="round" stroke-width="4" d="M15 17a9 9 0 0 1 18 0"/><circle cx="36" cy="12" r="8" fill="#d1fae5" stroke="#10b981" stroke-width="2"/><path fill="none" stroke="#059669" stroke-linecap="round" stroke-width="2" d="m32 12 3 3 5-6"/></svg>',
            'receivable' => '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48"><circle cx="31" cy="17" r="12" fill="#fb923c"/><path fill="#fff7ed" d="M31 9v16m4-12c0-4-8-4-8 0s8 3 8 7-8 4-9 0"/><path fill="#f97316" d="M5 31c5-5 9-6 14-2l5 4-4 5-6-3-6 6H5z"/><path fill="#fdba74" d="m18 31 5-4 8 1 8 7-3 4-9-4-6 3z"/></svg>',
            'margin' => '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48"><path fill="none" stroke="#19b981" stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M39 17A16 16 0 0 0 10 12l-3 4m0-8v8h8M9 31a16 16 0 0 0 29 5l3-4m0 8v-8h-8"/><circle cx="24" cy="24" r="9" fill="#d1fae5"/><path fill="#079669" d="M24 17v14m4-10c0-4-8-4-8 0s8 3 8 6-8 4-9 0"/></svg>',
            'operations' => '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48"><rect x="6" y="28" width="7" height="14" rx="2" fill="#fb7185"/><rect x="18" y="21" width="7" height="21" rx="2" fill="#f43f5e"/><rect x="30" y="13" width="7" height="29" rx="2" fill="#e11d48"/><path fill="none" stroke="#e11d48" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="m8 20 11-8 9 3L41 4m-9 0h9v9"/></svg>',
            'capital' => '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48"><path fill="#f5a18b" d="M3 28c5-8 10-9 17-4l5 4-4 6-8-4-6 7H3zm42 0c-5-8-10-9-17-4l-5 4 4 6 8-4 6 7h4z"/><path fill="#f5c84c" d="m24 15 13 12-13 13-13-13z"/><path fill="#4c82c3" d="M5 37h7v8H5zm31 0h7v8h-7z"/><circle cx="24" cy="27" r="5" fill="#fff2bf"/></svg>',
            'equity' => '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48"><path fill="#f7c948" d="M7 5h20v29H7z"/><path fill="#fff" d="M11 10h12v3H11zm0 6h12v3H11zm0 6h9v3h-9z"/><path fill="#10a66b" d="M22 16h20v27H22z"/><path fill="#d1fae5" d="M26 21h12v3H26zm0 6h12v3H26z"/><circle cx="32" cy="37" r="4" fill="#f8a53a"/></svg>',
            'profit' => '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48"><path fill="#9b6a35" d="M22 4h9l-2 6h-5z"/><path fill="#f3c34e" d="M18 10h17c0 12 8 16 8 23 0 7-8 11-20 11S3 40 3 33c0-7 11-11 15-23"/><path fill="#f49a70" d="M1 32h9v13H1zm37 0h9v13h-9z"/><path fill="#fff" d="M27 17v18m5-13c0-5-11-5-11 0s11 4 11 9-11 5-12 0"/></svg>',
        ])->map(fn (string $svg) => base64_encode($svg))->all();

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.pdf', [
            'sections' => $sections,
            'from' => $filters['from'],
            'to' => $filters['to'],
            'financial' => $financial,
            'cashCards' => $cashCards,
            'cashAccounts' => $cashAccounts,
            'assetBreakdown' => $assetBreakdown,
            'chartImage' => base64_encode($chartSvg),
            'iconImages' => $iconImages,
            'assetsTotal' => $assetsTotal,
            'liabilities' => $negativeCashBalances,
            'equity' => $equity,
            'savingMargin' => $savingMargin,
            'shipments' => $shipments,
        ])->setPaper('a4', 'landscape')
            ->download('laporan-'.$name.'-'.$period.'.pdf');
    }

    private function reportSections(array $filters, array $reportTypes): array
    {
        $types = $filters['type'] === 'all'
            ? array_diff(array_keys($reportTypes), ['all'])
            : [$filters['type']];
        $sections = [];

        foreach ($types as $type) {
            [$rows, $summary] = $this->reportData(array_merge($filters, ['type' => $type]));
            $sections[] = $this->formatReportSection($type, $rows, $summary);
        }

        return $sections;
    }

    private function formatReportSection(string $type, $rows, array $summary): array
    {
        if ($type === 'purchases') {
            return [
                'title' => 'Laporan Pembelian',
                'summary' => [
                    'Jumlah transaksi' => number_format($summary['transactions'], 0, ',', '.'),
                    'Total dibayarkan' => 'Rp '.number_format($summary['total'], 0, ',', '.'),
                ],
                'columns' => ['No. Nota', 'Tanggal', 'Petani', 'Kategori', 'Netto 2 (Kg)', 'Total Bayar (Rp)'],
                'rows' => $rows->map(fn (Purchase $purchase) => [
                    $purchase->no_nota,
                    $purchase->tanggal->format('d/m/Y'),
                    $purchase->petani,
                    $purchase->kategori,
                    number_format($purchase->netto_2, 2, ',', '.'),
                    'Rp '.number_format($purchase->total_bayar, 0, ',', '.'),
                ])->all(),
            ];
        }

        if ($type === 'shipments') {
            return [
                'title' => 'Laporan Pengiriman PKS',
                'summary' => [
                    'Jumlah pengiriman' => number_format($summary['transactions'], 0, ',', '.'),
                    'Total muatan' => number_format($summary['total_kg'], 2, ',', '.').' Kg',
                ],
                'columns' => ['No. Surat Jalan', 'Tanggal', 'PKS Tujuan', 'Nopol', 'Driver', 'Total (Kg)'],
                'rows' => $rows->map(fn (Shipment $shipment) => [
                    $shipment->no_nota,
                    $shipment->tanggal->format('d/m/Y'),
                    $shipment->pks_tujuan,
                    $shipment->nopol,
                    $shipment->driver,
                    number_format($shipment->total_kg, 2, ',', '.'),
                ])->all(),
            ];
        }

        if ($type === 'profit-loss') {
            return [
                'title' => 'Laporan Laba Rugi',
                'summary' => [
                    'Pendapatan penjualan PKS' => 'Rp '.number_format($summary['revenue'], 0, ',', '.'),
                    'HPP pembelian sawit' => 'Rp '.number_format($summary['cost_of_goods'], 0, ',', '.'),
                    'Beban langsung kategori 1–4' => 'Rp '.number_format($summary['additional_direct_expenses'], 0, ',', '.'),
                    'Total beban biaya langsung' => 'Rp '.number_format($summary['direct_expenses'], 0, ',', '.'),
                    'Biaya operasional kategori 5' => 'Rp '.number_format($summary['operational_expenses'], 0, ',', '.'),
                    'Total beban' => 'Rp '.number_format($summary['total_expenses'], 0, ',', '.'),
                    ($summary['net_profit'] < 0 ? 'Rugi bersih' : 'Laba bersih') => 'Rp '.number_format(abs($summary['net_profit']), 0, ',', '.'),
                ],
                'columns' => ['No. Transaksi', 'Tanggal', 'Komponen', 'Kategori / Tujuan', 'Keterangan', 'Nominal (Rp)'],
                'rows' => $rows->map(fn (array $row) => [
                    $row['number'],
                    $row['date']->format('d/m/Y'),
                    $row['type'],
                    $row['category'],
                    $row['description'],
                    ($row['direction'] === 'in' ? '+' : '-').' Rp '.number_format($row['amount'], 0, ',', '.'),
                ])->all(),
            ];
        }

        return [
            'title' => 'Laporan Arus Kas',
            'summary' => [
                'Total kas masuk' => 'Rp '.number_format($summary['total_in'], 0, ',', '.'),
                'Total kas keluar' => 'Rp '.number_format($summary['total_out'], 0, ',', '.'),
                'Saldo bersih' => 'Rp '.number_format($summary['net'], 0, ',', '.'),
            ],
            'columns' => ['No. Transaksi', 'Tanggal', 'Jenis', 'Kategori / Sumber', 'Keterangan', 'Nominal (Rp)'],
            'rows' => $rows->map(fn (array $row) => [
                $row['number'],
                $row['date']->format('d/m/Y'),
                $row['direction'] === 'in' ? 'Kas Masuk' : 'Kas Keluar',
                $row['category'],
                $row['description'],
                ($row['direction'] === 'in' ? '+' : '-').' Rp '.number_format($row['amount'], 0, ',', '.'),
            ])->all(),
        ];
    }

    private function reportData(array $filters): array
    {
        if ($filters['type'] === 'profit-loss') {
            return $this->profitLossReportData($filters);
        }

        if ($filters['type'] === 'purchases') {
            $rows = Purchase::query()
                ->whereDate('tanggal', '>=', $filters['from'])
                ->whereDate('tanggal', '<=', $filters['to'])
                ->orderBy('tanggal')
                ->orderBy('id')
                ->get();

            return [$rows, [
                'transactions' => $rows->count(),
                'total' => (float) $rows->sum('total_bayar'),
            ]];
        }

        if ($filters['type'] === 'shipments') {
            $rows = Shipment::query()
                ->whereDate('tanggal', '>=', $filters['from'])
                ->whereDate('tanggal', '<=', $filters['to'])
                ->orderBy('tanggal')
                ->orderBy('id')
                ->get();

            return [$rows, [
                'transactions' => $rows->count(),
                'total_kg' => (float) $rows->sum('total_kg'),
            ]];
        }

        $inflows = CashInflow::query()
            ->where(function ($query) {
                $query->whereNull('cash_account_id')
                    ->orWhereHas('cashAccount', fn ($query) => $query->whereNull('owner_id'));
            })
            ->whereDate('tanggal', '>=', $filters['from'])
            ->whereDate('tanggal', '<=', $filters['to'])
            ->get()
            ->map(fn (CashInflow $inflow) => [
                'id' => $inflow->id,
                'date' => $inflow->tanggal,
                'number' => $inflow->no_transaksi,
                'category' => $inflow->sumber,
                'description' => $inflow->keterangan,
                'amount' => (float) $inflow->nominal,
                'direction' => 'in',
            ]);
        $receivablePayments = ReceivableTransaction::query()
            ->with('receivable:id,pelanggan,no_transaksi')
            ->where('jenis', ReceivableTransaction::TYPE_PAYMENT)
            ->whereDate('tanggal', '>=', $filters['from'])
            ->whereDate('tanggal', '<=', $filters['to'])
            ->get()
            ->map(fn (ReceivableTransaction $payment) => [
                'id' => $payment->id,
                'date' => $payment->tanggal,
                'number' => $payment->no_pembayaran,
                'category' => 'Cicilan Piutang - '.$payment->receivable->pelanggan,
                'description' => 'Pembayaran '.$payment->receivable->no_transaksi.' via '.$payment->metode,
                'amount' => (float) $payment->nominal,
                'direction' => 'in',
            ]);
        $shipmentReceipts = Shipment::query()
            ->where('is_paid', true)
            ->whereDate('paid_at', '>=', $filters['from'])
            ->whereDate('paid_at', '<=', $filters['to'])
            ->get()
            ->map(fn (Shipment $shipment) => [
                'id' => $shipment->id,
                'date' => $shipment->paid_at,
                'number' => $shipment->no_nota,
                'category' => 'Penjualan PKS - '.$shipment->pks_tujuan,
                'description' => number_format((float) $shipment->total_kg, 2, ',', '.').' Kg',
                'amount' => (float) $shipment->total_pendapatan,
                'direction' => 'in',
            ]);
        $outflows = CashOutflow::query()
            ->whereDate('tanggal', '>=', $filters['from'])
            ->whereDate('tanggal', '<=', $filters['to'])
            ->get()
            ->map(fn (CashOutflow $outflow) => [
                'id' => $outflow->id,
                'date' => $outflow->tanggal,
                'number' => $outflow->no_transaksi,
                'category' => $outflow->kategori,
                'description' => $outflow->keterangan,
                'amount' => (float) $outflow->nominal,
                'direction' => 'out',
            ]);
        $purchasePayments = Purchase::query()
            ->whereDate('tanggal', '>=', $filters['from'])
            ->whereDate('tanggal', '<=', $filters['to'])
            ->get()
            ->map(fn (Purchase $purchase) => [
                'id' => $purchase->id,
                'date' => $purchase->tanggal,
                'number' => $purchase->no_nota,
                'category' => 'Pembelian - '.$purchase->kategori,
                'description' => 'Pembayaran pembelian '.$purchase->petani,
                'amount' => (float) $purchase->total_pembelian,
                'direction' => 'out',
            ]);
        $rows = $inflows->concat($receivablePayments)->concat($shipmentReceipts)->concat($outflows)->concat($purchasePayments)
            ->sortBy(fn (array $row) => [$row['date']->format('Y-m-d'), $row['id']])
            ->values();
        $totalIn = (float) $inflows->sum('amount')
            + (float) $receivablePayments->sum('amount')
            + (float) $shipmentReceipts->sum('amount');
        $totalOut = (float) $outflows->sum('amount') + (float) $purchasePayments->sum('amount');

        return [$rows, [
            'transactions' => $rows->count(),
            'total_in' => $totalIn,
            'total_out' => $totalOut,
            'net' => $totalIn - $totalOut,
        ]];
    }

    private function profitLossReportData(array $filters): array
    {
        $shipments = Shipment::query()
            ->whereDate('tanggal', '>=', $filters['from'])
            ->whereDate('tanggal', '<=', $filters['to'])
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();
        $purchases = Purchase::query()
            ->whereDate('tanggal', '>=', $filters['from'])
            ->whereDate('tanggal', '<=', $filters['to'])
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();
        $expenseClassifications = array_merge(
            CashOutflow::EXPENSE_CATEGORIES,
            CashOutflow::LEGACY_EXPENSE_CLASSIFICATIONS
        );
        $expenses = CashOutflow::query()
            ->whereDate('tanggal', '>=', $filters['from'])
            ->whereDate('tanggal', '<=', $filters['to'])
            ->whereIn('kategori', array_keys($expenseClassifications))
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        $revenueRows = $shipments->map(fn (Shipment $shipment) => [
            'date' => $shipment->tanggal,
            'number' => $shipment->no_nota,
            'type' => 'Pendapatan Penjualan PKS',
            'category' => $shipment->pks_tujuan,
            'description' => number_format((float) $shipment->total_kg, 2, ',', '.').' Kg x Rp '
                .number_format((float) $shipment->harga_jual_per_kg, 0, ',', '.'),
            'amount' => (float) $shipment->total_pendapatan,
            'direction' => 'in',
        ]);
        $purchaseRows = $purchases->map(fn (Purchase $purchase) => [
            'date' => $purchase->tanggal,
            'number' => $purchase->no_nota,
            'type' => 'HPP - Pembelian Sawit',
            'category' => $purchase->kategori,
            'description' => 'Pembelian dari '.$purchase->petani,
            'amount' => (float) $purchase->total_bayar,
            'direction' => 'out',
        ]);
        $expenseRows = $expenses->map(fn (CashOutflow $expense) => [
            'date' => $expense->tanggal,
            'number' => $expense->no_transaksi,
            'type' => $expenseClassifications[$expense->kategori] === 'direct'
                ? 'Beban Biaya Langsung'
                : 'Biaya Operasional',
            'category' => $expense->kategori,
            'description' => $expense->keterangan,
            'amount' => (float) $expense->nominal,
            'direction' => 'out',
        ]);

        $directExpenseCategories = array_keys(array_filter(
            $expenseClassifications,
            fn (string $classification) => $classification === 'direct'
        ));
        $operationalExpenseCategories = array_keys(array_filter(
            $expenseClassifications,
            fn (string $classification) => $classification === 'operational'
        ));
        $costOfGoods = (float) $purchases->sum('total_pembelian');
        $additionalDirectExpenses = (float) $expenses
            ->whereIn('kategori', $directExpenseCategories)
            ->sum('nominal');
        $operationalExpenses = (float) $expenses
            ->whereIn('kategori', $operationalExpenseCategories)
            ->sum('nominal');
        $directExpenses = $costOfGoods + $additionalDirectExpenses;
        $totalExpenses = $directExpenses + $operationalExpenses;
        $revenue = (float) $shipments->sum('total_pendapatan');

        return [
            $revenueRows->concat($purchaseRows)->concat($expenseRows)
                ->sortBy(fn (array $row) => [$row['date']->format('Y-m-d'), $row['number']])
                ->values(),
            [
                'revenue' => $revenue,
                'cost_of_goods' => $costOfGoods,
                'additional_direct_expenses' => $additionalDirectExpenses,
                'direct_expenses' => $directExpenses,
                'operational_expenses' => $operationalExpenses,
                'total_expenses' => $totalExpenses,
                'net_profit' => $revenue - $totalExpenses,
            ],
        ];
    }
}
