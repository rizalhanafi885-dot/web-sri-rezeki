<?php

namespace App\Http\Controllers;

use App\Models\CashAccount;
use App\Models\CashInflow;
use App\Models\CashOutflow;
use App\Models\CashTransfer;
use App\Models\Purchase;
use App\Models\Receivable;
use App\Models\ReceivableTransaction;
use App\Models\Shipment;
use App\Services\CashAccountBalanceService;
use App\Services\InventoryValuationService;
use App\Services\OwnerFinancialReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OwnerDashboardController extends Controller
{
    public function index(
        Request $request,
        CashAccountBalanceService $balanceService,
        OwnerFinancialReportService $reportService,
        InventoryValuationService $valuationService,
        string $page = 'dashboard'
    )
    {
        $hasPeriodFilter = $request->query->has('from') || $request->query->has('to');
        $period = $hasPeriodFilter
            ? $request->validate([
                'from' => ['required', 'date'],
                'to' => ['required', 'date', 'after_or_equal:from'],
            ])
            : ['from' => now()->startOfMonth()->toDateString(), 'to' => now()->toDateString()];

        $ownerId = (int) $request->user()->getKey();
        $mainCashAccount = CashAccount::query()->firstOrCreate(
            ['owner_id' => $ownerId, 'name' => 'Kas Utama'],
            ['type' => 'cash', 'initial_balance' => 0]
        );
        $accounts = CashAccount::query()
            ->where('owner_id', $ownerId)
            ->orderByRaw("CASE WHEN type = 'cash' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();
        $adminRamAccount = CashAccount::query()->firstOrCreate(
            ['owner_id' => null, 'name' => 'KAS RAM'],
            ['type' => 'cash', 'initial_balance' => 0]
        );
        $balances = $balanceService->balances(null, $ownerId);
        $accounts->each(fn (CashAccount $account) => $account->setAttribute(
            'current_balance',
            $balances[$account->id] ?? (float) $account->initial_balance
        ));

        $salesToday = Shipment::query()->whereDate('tanggal', today())->get();
        $salesPeriod = Shipment::query()
            ->whereDate('tanggal', '>=', $period['from'])
            ->whereDate('tanggal', '<=', $period['to'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();
        $unpaidSales = Shipment::query()
            ->where('is_paid', false)
            ->where('total_pendapatan', '>', 0)
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();
        $periodPurchases = Purchase::query()
            ->whereDate('tanggal', '>=', $period['from'])
            ->whereDate('tanggal', '<=', $period['to'])
            ->get();
        $classifications = array_merge(
            CashOutflow::EXPENSE_CATEGORIES,
            CashOutflow::LEGACY_EXPENSE_CLASSIFICATIONS
        );
        $periodExpenses = CashOutflow::query()
            ->whereDate('tanggal', '>=', $period['from'])
            ->whereDate('tanggal', '<=', $period['to'])
            ->whereIn('kategori', array_keys($classifications))
            ->get();
        $revenue = (float) $salesPeriod->sum('total_pendapatan');
        $costOfGoods = (float) $periodPurchases->sum('total_pembelian');
        $directExpenseCategories = array_keys(array_filter(
            $classifications,
            fn (string $classification) => $classification === 'direct'
        ));
        $operationalExpenseCategories = array_keys(array_filter(
            $classifications,
            fn (string $classification) => $classification === 'operational'
        ));
        $directExpenses = $costOfGoods
            + (float) $periodExpenses->whereIn('kategori', $directExpenseCategories)->sum('nominal');
        $operationalExpenses = (float) $periodExpenses
            ->whereIn('kategori', $operationalExpenseCategories)
            ->sum('nominal');
        $netProfit = $revenue - $directExpenses - $operationalExpenses;

        $stocks = Shipment::availableStock();
        $purchaseCosts = Purchase::query()
            ->selectRaw('kategori, SUM(netto_1) as total_netto_1, SUM(netto_2 * harga_per_kg) as total_cost')
            ->whereIn('kategori', array_keys(Shipment::CARGO_FIELDS))
            ->groupBy('kategori')
            ->get()
            ->keyBy('kategori');
        $inventoryValue = 0.0;
        foreach ($stocks as $category => $weight) {
            $cost = $purchaseCosts->get($category);
            $inventoryValue += $valuationService->remainingValue(
                (float) $weight,
                (float) ($cost->total_netto_1 ?? 0),
                (float) ($cost->total_cost ?? 0)
            );
        }
        $receivables = (float) Receivable::sum('nominal_awal')
            + (float) ReceivableTransaction::where('jenis', ReceivableTransaction::TYPE_LOAN)->sum('nominal')
            - (float) ReceivableTransaction::where('jenis', ReceivableTransaction::TYPE_PAYMENT)->sum('nominal')
            + (float) $unpaidSales->sum('total_pendapatan');
        $cashAndBank = array_sum($balances);
        $totalAssets = $cashAndBank + $inventoryValue + max(0, $receivables);
        $financialReport = $page === 'reports'
            ? $reportService->generate($period['from'], $period['to'], $balanceService, $ownerId)
            : null;

        return view('owner.dashboard', [
            'user' => $request->user(),
            'page' => $page,
            'accounts' => $accounts,
            'mainCashAccount' => $mainCashAccount,
            'transferDestinationAccounts' => collect([$adminRamAccount]),
            'totalCashBalance' => $cashAndBank,
            'salesToday' => $salesToday,
            'salesPeriod' => $salesPeriod,
            'unpaidSales' => $unpaidSales,
            'period' => $period,
            'periodSummary' => $financialReport['summary'] ?? [
                'revenue' => $revenue,
                'tonnage' => (float) $salesPeriod->sum('total_kg'),
                'direct_expenses' => $directExpenses,
                'operational_expenses' => $operationalExpenses,
                'net_profit' => $netProfit,
            ],
            'todaySummary' => [
                'revenue' => (float) $salesToday->sum('total_pendapatan'),
                'tonnage' => (float) $salesToday->sum('total_kg'),
            ],
            'balanceSheet' => $financialReport['balance_sheet'] ?? [
                'cash' => $cashAndBank,
                'inventory' => $inventoryValue,
                'receivables' => max(0, $receivables),
                'liabilities' => 0,
                'equity' => $totalAssets,
                'total_assets' => $totalAssets,
            ],
            'financialReport' => $financialReport,
            'transfers' => CashTransfer::query()
                ->with(['sourceAccount:id,name', 'destinationAccount:id,name'])
                ->where(function ($query) use ($ownerId) {
                    $query->whereHas('sourceAccount', fn ($query) => $query->where('owner_id', $ownerId))
                        ->orWhereHas('destinationAccount', fn ($query) => $query->where('owner_id', $ownerId));
                })
                ->orderByDesc('tanggal')
                ->orderByDesc('id')
                ->limit(10)
                ->get(),
            'expenseCategories' => CashOutflow::EXPENSE_CATEGORIES,
        ]);
    }

    public function cash(Request $request, CashAccountBalanceService $balanceService)
    {
        return $this->index(
            $request,
            $balanceService,
            app(OwnerFinancialReportService::class),
            app(InventoryValuationService::class),
            'cash'
        );
    }

    public function sales(
        Request $request,
        CashAccountBalanceService $balanceService,
        OwnerFinancialReportService $reportService
    )
    {
        return $this->index($request, $balanceService, $reportService, app(InventoryValuationService::class), 'sales');
    }

    public function expenses(
        Request $request,
        CashAccountBalanceService $balanceService,
        OwnerFinancialReportService $reportService
    )
    {
        return $this->index($request, $balanceService, $reportService, app(InventoryValuationService::class), 'expenses');
    }

    public function reports(
        Request $request,
        CashAccountBalanceService $balanceService,
        OwnerFinancialReportService $reportService
    )
    {
        return $this->index($request, $balanceService, $reportService, app(InventoryValuationService::class), 'reports');
    }

    public function exportReport(
        Request $request,
        string $format,
        OwnerFinancialReportService $reportService,
        CashAccountBalanceService $balanceService
    ) {
        abort_unless(in_array($format, ['pdf', 'csv'], true), 404);

        $period = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);
        $report = $reportService->generate(
            $period['from'],
            $period['to'],
            $balanceService,
            (int) $request->user()->getKey()
        );

        if ($format === 'pdf') {
            return \Barryvdh\DomPDF\Facade\Pdf::loadView('owner.reports.pdf', compact('report'))
                ->setPaper('a4', 'landscape')
                ->download('laporan-keuangan-'.$period['from'].'-'.$period['to'].'.pdf');
        }

        return response()->streamDownload(function () use ($report) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['LAPORAN KEUANGAN SUMBER REZEKI'], ';');
            fputcsv($output, ['Periode', $report['from'].' s.d. '.$report['to']], ';');
            fputcsv($output, [], ';');
            fputcsv($output, ['RINGKASAN LABA RUGI'], ';');
            fputcsv($output, ['Pendapatan penjualan PKS', $report['summary']['revenue']], ';');
            fputcsv($output, ['HPP pembelian sawit', $report['summary']['cost_of_goods']], ';');
            fputcsv($output, ['Beban langsung lainnya', $report['summary']['additional_direct_expenses']], ';');
            fputcsv($output, ['Total beban langsung', $report['summary']['direct_expenses']], ';');
            fputcsv($output, ['Biaya operasional', $report['summary']['operational_expenses']], ';');
            fputcsv($output, ['Laba / (Rugi) bersih', $report['summary']['net_profit']], ';');
            fputcsv($output, [], ';');
            fputcsv($output, ['NERACA PER '.$report['to']], ';');
            fputcsv($output, ['Kas dan bank', $report['balance_sheet']['cash']], ';');
            fputcsv($output, ['Persediaan (estimasi HPP)', $report['balance_sheet']['inventory']], ';');
            fputcsv($output, ['Piutang', $report['balance_sheet']['receivables']], ';');
            fputcsv($output, ['Total aset', $report['balance_sheet']['total_assets']], ';');
            fputcsv($output, ['Kewajiban', $report['balance_sheet']['liabilities']], ';');
            fputcsv($output, ['Modal / saldo bersih', $report['balance_sheet']['equity']], ';');
            fputcsv($output, [], ';');
            fputcsv($output, [
                'Tanggal', 'No. Referensi', 'Jenis Transaksi', 'Kategori / Pihak',
                'Keterangan', 'Jumlah (Rp)', 'Arus', 'Status / Akun',
            ], ';');
            foreach ($report['rows'] as $row) {
                fputcsv($output, [
                    $row['date'],
                    $this->spreadsheetSafe($row['number']),
                    $this->spreadsheetSafe($row['type']),
                    $this->spreadsheetSafe($row['category']),
                    $this->spreadsheetSafe($row['description']),
                    $row['amount'],
                    $this->spreadsheetSafe($row['flow']),
                    $this->spreadsheetSafe($row['status']),
                ], ';');
            }
            fclose($output);
        }, 'laporan-keuangan-'.$period['from'].'-'.$period['to'].'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function spreadsheetSafe(string $value): string
    {
        return preg_match('/^[\s]*[=+\-@]/u', $value) ? "'".$value : $value;
    }

    public function storeAccount(Request $request)
    {
        $request->merge(['name' => trim((string) $request->input('name'))]);
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cash_accounts', 'name')->where('owner_id', $request->user()->getKey()),
            ],
            'initial_balance' => ['required', 'integer', 'min:0', 'max:999999999999'],
        ]);

        CashAccount::create([
            'owner_id' => $request->user()->getKey(),
            'name' => trim($data['name']),
            'type' => 'bank',
            'initial_balance' => $data['initial_balance'],
        ]);

        return redirect()->route('owner.cash')->with('success', 'Rekening berhasil ditambahkan.');
    }

    public function addCashBalance(Request $request)
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'nominal' => ['required', 'integer', 'min:1', 'max:999999999999'],
            'keterangan' => ['nullable', 'string', 'max:5000'],
        ]);
        $ownerId = (int) $request->user()->getKey();

        DB::transaction(function () use ($ownerId, $data) {
            $account = CashAccount::query()
                ->where('owner_id', $ownerId)
                ->where('type', 'cash')
                ->where('name', 'Kas Utama')
                ->lockForUpdate()
                ->firstOrFail();
            CashInflow::create([
                'no_transaksi' => CashInflowController::nextTransactionNumber($data['tanggal']),
                'cash_account_id' => $account->id,
                'tanggal' => $data['tanggal'],
                'sumber' => 'Setoran Owner',
                'nominal' => $data['nominal'],
                'keterangan' => $data['keterangan'] ?? 'Penambahan saldo Kas Utama Owner',
            ]);
        });

        return redirect()->route('owner.cash')->with('success', 'Saldo Kas Utama Owner berhasil ditambahkan.');
    }

    public function storeTransfer(Request $request)
    {
        $ownerId = (int) $request->user()->getKey();
        $sourceAccount = CashAccount::query()
            ->where('owner_id', $ownerId)
            ->where('type', 'cash')
            ->where('name', 'Kas Utama')
            ->firstOrFail();
        $destinationAccount = CashAccount::query()->firstOrCreate(
            ['owner_id' => null, 'name' => 'KAS RAM'],
            ['type' => 'cash', 'initial_balance' => 0]
        );

        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'destination_account_id' => ['required', Rule::in([$destinationAccount->id])],
            'nominal' => ['required', 'integer', 'min:1', 'max:999999999999'],
            'keterangan' => ['nullable', 'string', 'max:5000'],
        ]);

        $transfer = DB::transaction(function () use ($data, $sourceAccount, $destinationAccount) {
            CashAccount::query()
                ->whereIn('id', [$sourceAccount->id, $destinationAccount->id])
                ->orderBy('id')
                ->lockForUpdate()
                ->get(['id']);
            $datePrefix = 'TF-'.Carbon::parse($data['tanggal'])->format('ymd').'-';
            $lastNumber = CashTransfer::where('no_transaksi', 'like', $datePrefix.'%')
                ->pluck('no_transaksi')
                ->map(fn (string $number) => (int) substr($number, strrpos($number, '-') + 1))
                ->max();

            $data['no_transaksi'] = $datePrefix.str_pad((string) ((int) $lastNumber + 1), 3, '0', STR_PAD_LEFT);
            $data['source_account_id'] = $sourceAccount->id;

            return CashTransfer::create($data);
        });

        return redirect()->route('owner.cash')->with(
            'success',
            'Transfer '.$transfer->no_transaksi.' berhasil dicatat.'
        );
    }

    public function storeExpense(Request $request)
    {
        $ownerId = $request->user()->getKey();
        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'cash_account_id' => [
                'required',
                'integer',
                Rule::exists('cash_accounts', 'id')->where('owner_id', $ownerId),
            ],
            'kategori' => ['required', Rule::in(array_keys(CashOutflow::EXPENSE_CATEGORIES))],
            'nominal' => ['required', 'integer', 'min:1', 'max:999999999999'],
            'keterangan' => ['required', 'string', 'max:5000'],
        ]);

        $outflow = DB::transaction(function () use ($data) {
            $data['no_transaksi'] = CashOutflowController::nextTransactionNumber($data['tanggal']);

            return CashOutflow::create($data);
        });

        return redirect()->route('owner.expenses')->with(
            'success',
            'Pengeluaran '.$outflow->no_transaksi.' berhasil disimpan.'
        );
    }

    public function recordSalePayment(Request $request, Shipment $shipment)
    {
        $ownerId = $request->user()->getKey();
        $data = $request->validate([
            'paid_at' => ['required', 'date'],
            'cash_account_id' => [
                'required',
                'integer',
                Rule::exists('cash_accounts', 'id')->where('owner_id', $ownerId),
            ],
        ]);

        DB::transaction(function () use ($data, $shipment) {
            $lockedShipment = Shipment::whereKey($shipment->id)->lockForUpdate()->firstOrFail();
            if ($lockedShipment->is_paid || (float) $lockedShipment->total_pendapatan <= 0) {
                throw ValidationException::withMessages([
                    'shipment' => 'Penjualan ini sudah lunas atau belum memiliki nilai pendapatan yang valid.',
                ]);
            }

            $lockedShipment->update([
                'is_paid' => true,
                'paid_at' => $data['paid_at'],
                'cash_account_id' => $data['cash_account_id'],
            ]);
        });

        return redirect()->route('owner.sales')
            ->with('success', 'Pelunasan penjualan '.$shipment->no_nota.' berhasil dicatat.');
    }
}
