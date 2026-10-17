<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Shipment;
use App\Models\StockOpname;
use App\Services\InventoryValuationService;

class StockController extends Controller
{
    public function index(InventoryValuationService $valuationService)
    {
        $categories = Shipment::CARGO_FIELDS;
        $purchaseTotals = Purchase::query()
            ->selectRaw(
                'kategori, SUM(netto_1) as total_masuk, '
                .'SUM(netto_2) as total_netto_2, '
                .'SUM(netto_2 * harga_per_kg) as total_nilai_beli'
            )
            ->whereIn('kategori', array_keys($categories))
            ->groupBy('kategori')
            ->get()
            ->keyBy('kategori');

        $shipmentTotals = Shipment::query()
            ->selectRaw(implode(', ', array_map(
                fn (string $field) => "COALESCE(SUM({$field}), 0) as {$field}",
                array_values($categories)
            )))
            ->first();
        $adjustmentTotals = StockOpname::query()
            ->selectRaw('kategori, SUM(selisih_kg) as total_selisih')
            ->whereIn('kategori', array_keys($categories))
            ->groupBy('kategori')
            ->get()
            ->keyBy('kategori');

        $stockRows = collect($categories)
            ->map(function (string $field, string $category) use (
                $purchaseTotals,
                $shipmentTotals,
                $adjustmentTotals,
                $valuationService
            ) {
                $purchases = $purchaseTotals->get($category);
                $totalMasuk = (float) ($purchases->total_masuk ?? 0);
                $totalNetto2 = (float) ($purchases->total_netto_2 ?? 0);
                $totalNilaiBeli = (float) ($purchases->total_nilai_beli ?? 0);
                $totalKeluar = (float) $shipmentTotals->{$field};
                $penyesuaian = (float) ($adjustmentTotals->get($category)->total_selisih ?? 0);
                $stokTersedia = $totalMasuk - $totalKeluar + $penyesuaian;

                return [
                    'kategori' => $category,
                    'total_masuk' => $totalMasuk,
                    'total_netto_2' => $totalNetto2,
                    'total_keluar' => $totalKeluar,
                    'penyesuaian' => $penyesuaian,
                    'stok_tersedia' => $stokTersedia,
                    'estimasi_nilai' => $valuationService->remainingValue(
                        $stokTersedia,
                        $totalMasuk,
                        $totalNilaiBeli
                    ),
                ];
            });

        $totalEstimasiNilai = $stockRows->sum('estimasi_nilai');
        $totalStokTersedia = $stockRows->sum('stok_tersedia');

        return view('admin.stocks.index', compact(
            'stockRows',
            'totalEstimasiNilai',
            'totalStokTersedia'
        ));
    }
}
