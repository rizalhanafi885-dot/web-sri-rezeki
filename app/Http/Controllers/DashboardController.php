<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Receivable;
use App\Models\ReceivableTransaction;
use App\Models\Shipment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Services\CashAccountBalanceService;
use App\Services\InventoryValuationService;

class DashboardController extends Controller
{
    /**
     * Dashboard Operasional Admin
     */
    public function adminDashboard(InventoryValuationService $valuationService)
    {
        $user = Auth::user();
        $stok = Shipment::availableStock();
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $purchaseToday = Purchase::query()
            ->whereDate('tanggal', $today)
            ->selectRaw('COALESCE(SUM(netto_1), 0) as total_kg, COALESCE(SUM(total_bayar), 0) as total_bayar')
            ->first();
        $shipmentToday = Shipment::query()
            ->whereDate('tanggal', $today)
            ->sum('total_kg');
        $cashBalance = array_sum(app(CashAccountBalanceService::class)->balances());
        $loanTotal = (float) ReceivableTransaction::where('jenis', ReceivableTransaction::TYPE_LOAN)->sum('nominal');
        $paymentTotal = (float) ReceivableTransaction::where('jenis', ReceivableTransaction::TYPE_PAYMENT)->sum('nominal');
        $receivableBalance = (float) Receivable::sum('nominal_awal') + $loanTotal - $paymentTotal;

        $kpi = [
            'saldo_kas' => ($cashBalance < 0 ? '-Rp ' : 'Rp ')
                .number_format(abs($cashBalance), 0, ',', '.'),
            'saldo_kas_desc' => 'Total kas tersedia',
            'pembelian_hari_ini_kg' => number_format((float) $purchaseToday->total_kg, 0, ',', '.').' Kg',
            'pembelian_hari_ini_rp' => 'Rp '.number_format((float) $purchaseToday->total_bayar, 0, ',', '.'),
            'pengiriman_hari_ini_kg' => number_format((float) $shipmentToday, 0, ',', '.').' Kg',
            'pengiriman_hari_ini_desc' => 'Total tonase dikirim',
            'stok_tbs_tersedia' => number_format($stok['TBS'], 0, ',', '.').' Kg',
            'stok_tbs_desc' => 'Netto 1 · Siap kirim',
            'total_piutang' => 'Rp '.number_format(max(0, $receivableBalance), 0, ',', '.'),
            'total_piutang_desc' => 'Belum dilunasi',
        ];

        $categories = Shipment::CARGO_FIELDS;
        $purchaseTotals = Purchase::query()
            ->selectRaw('kategori, SUM(netto_1) as total_netto_1, SUM(netto_2 * harga_per_kg) as total_nilai_beli')
            ->whereIn('kategori', array_keys($categories))
            ->groupBy('kategori')
            ->get()
            ->keyBy('kategori');

        $ringkasan_stok = collect($categories)
            ->map(function (string $field, string $category) use ($stok, $purchaseTotals, $valuationService) {
                $purchases = $purchaseTotals->get($category);
                $stock = (float) ($stok[$category] ?? 0);
                $purchasedNetto1 = (float) ($purchases->total_netto_1 ?? 0);
                $totalPurchaseValue = (float) ($purchases->total_nilai_beli ?? 0);

                return [
                    'nama' => $category,
                    'nilai' => $valuationService->remainingValue($stock, $purchasedNetto1, $totalPurchaseValue),
                    'berat' => number_format($stock, 0, ',', '.').' Kg',
                ];
            })
            ->values()
            ->all();

        $totalStockValue = collect($ringkasan_stok)->sum('nilai');
        $total_nilai_stok = $totalStockValue;

        $purchaseByWeek = array_fill(0, 4, 0.0);
        Purchase::query()
            ->whereBetween('tanggal', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get(['tanggal', 'netto_1'])
            ->each(function (Purchase $purchase) use (&$purchaseByWeek) {
                $week = min(3, intdiv(Carbon::parse($purchase->tanggal)->day - 1, 7));
                $purchaseByWeek[$week] += (float) $purchase->netto_1 / 1000;
            });

        $shipmentByWeek = array_fill(0, 4, 0.0);
        Shipment::query()
            ->whereBetween('tanggal', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get(['tanggal', 'total_kg'])
            ->each(function (Shipment $shipment) use (&$shipmentByWeek) {
                $week = min(3, intdiv(Carbon::parse($shipment->tanggal)->day - 1, 7));
                $shipmentByWeek[$week] += (float) $shipment->total_kg / 1000;
            });

        $chartData = [
            'purchases' => array_map(fn (float $value) => round($value, 2), $purchaseByWeek),
            'shipments' => array_map(fn (float $value) => round($value, 2), $shipmentByWeek),
        ];

        return view('admin.dashboard', compact(
            'user',
            'kpi',
            'ringkasan_stok',
            'total_nilai_stok',
            'chartData'
        ));
    }

}
