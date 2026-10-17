<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Shipment;
use App\Models\StockOpname;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StockOpnameController extends Controller
{
    public function index()
    {
        $opnames = StockOpname::orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();
        $stocks = Shipment::availableStock();
        $categories = array_keys(Shipment::CARGO_FIELDS);
        $opnameRecords = $opnames->mapWithKeys(fn (StockOpname $opname) => [
            $opname->id => [
                'id' => $opname->id,
                'kategori' => $opname->kategori,
                'stok_sistem_kg' => $opname->stok_sistem_kg,
                'stok_fisik_kg' => $opname->stok_fisik_kg,
                'selisih_kg' => $opname->selisih_kg,
            ],
        ]);

        return view('admin.stock-opnames.index', compact(
            'opnames',
            'stocks',
            'categories',
            'opnameRecords'
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validatedOpname($request);

        DB::transaction(function () use ($data, $request) {
            $this->lockInventory();
            $systemStock = Shipment::availableStock()[$data['kategori']];
            $data['stok_sistem_kg'] = $systemStock;
            $data['selisih_kg'] = round($data['stok_fisik_kg'] - $systemStock, 2);
            $data['petugas'] = $request->user()->name;

            StockOpname::create($data);
        });

        return redirect()
            ->route('admin.stock-opnames.index')
            ->with('success', 'Stok opname berhasil disimpan.');
    }

    public function update(Request $request, StockOpname $stockOpname)
    {
        $data = $this->validatedOpname($request);

        DB::transaction(function () use ($data, $request, $stockOpname) {
            $this->lockInventory();
            $systemStock = Shipment::availableStock(null, $stockOpname)[$data['kategori']];
            $data['stok_sistem_kg'] = $systemStock;
            $data['selisih_kg'] = round($data['stok_fisik_kg'] - $systemStock, 2);
            $data['petugas'] = $request->user()->name;

            $stockOpname->update($data);
        });

        return redirect()
            ->route('admin.stock-opnames.index')
            ->with('success', 'Stok opname berhasil diperbarui.');
    }

    private function validatedOpname(Request $request): array
    {
        return $request->validate([
            'tanggal' => ['required', 'date'],
            'kategori' => ['required', 'string', Rule::in(array_keys(Shipment::CARGO_FIELDS))],
            'stok_fisik_kg' => [
                'required',
                'numeric',
                'decimal:0,2',
                'min:0',
                'max:9999999999.99',
            ],
        ]);
    }

    private function lockInventory(): void
    {
        Purchase::whereIn('kategori', array_keys(Shipment::CARGO_FIELDS))
            ->orderBy('id')
            ->lockForUpdate()
            ->get(['id']);

        Shipment::orderBy('id')->lockForUpdate()->get(['id']);
        StockOpname::orderBy('id')->lockForUpdate()->get(['id']);
    }
}
