<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Shipment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ShipmentController extends Controller
{
    public function index()
    {
        $shipments = Shipment::with('cashAccount:id,name')
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();
        $stocks = Shipment::availableStock();

        return view('admin.shipments.index', compact('shipments', 'stocks'));
    }

    public function store(Request $request)
    {
        $this->createShipment($request);

        return redirect()
            ->route('admin.shipments.index')
            ->with('success', 'Pengiriman berhasil disimpan.');
    }

    public function ownerStore(Request $request)
    {
        $shipment = $this->createShipment($request);
        $saleDate = Carbon::parse($shipment->tanggal);

        return redirect()
            ->route('owner.sales', [
                'from' => $saleDate->copy()->startOfMonth()->toDateString(),
                'to' => $saleDate->toDateString(),
            ])
            ->with('success', 'Penjualan '.$shipment->no_nota.' berhasil disimpan dan stok diperbarui.');
    }

    public function update(Request $request, Shipment $shipment)
    {
        $data = $this->validatedShipment($request);

        DB::transaction(function () use ($data, $shipment) {
            $this->lockInventory();
            $this->ensureStockAvailable($data, $shipment);
            $shipment->update($data);
        });

        return redirect()
            ->route('admin.shipments.index')
            ->with('success', 'Pengiriman berhasil diperbarui.');
    }

    public function print(Shipment $shipment)
    {
        return view('admin.shipments.print', compact('shipment'));
    }

    private function validatedShipment(Request $request): array
    {
        $rules = [
            'tanggal' => ['required', 'date'],
            'pks_tujuan' => ['required', 'string', 'max:255'],
            'nopol' => ['required', 'string', 'max:30'],
            'driver' => ['required', 'string', 'max:100'],
            'harga_jual_per_kg' => ['required', 'integer', 'min:1', 'max:999999'],
            'catatan' => ['nullable', 'string'],
        ];

        foreach (Shipment::CARGO_FIELDS as $field) {
            $rules[$field] = [
                'required',
                'numeric',
                'decimal:0,2',
                'min:0',
                'max:9999999999.99',
            ];
        }

        $validator = Validator::make($request->all(), $rules);
        $validator->after(function ($validator) use ($request) {
            $total = collect(Shipment::CARGO_FIELDS)
                ->sum(fn (string $field) => (float) $request->input($field, 0));

            if ($total <= 0) {
                $validator->errors()->add('tbs_kg', 'Total muatan harus lebih dari 0 Kg.');
            }

            if ($total > 9999999999.99) {
                $validator->errors()->add('tbs_kg', 'Total muatan melebihi batas maksimum.');
            }
        });

        $data = $validator->validate();
        foreach (Shipment::CARGO_FIELDS as $field) {
            $data[$field] = round((float) $data[$field], 2);
        }
        $data['total_kg'] = round(collect(Shipment::CARGO_FIELDS)
            ->sum(fn (string $field) => $data[$field]), 2);
        $data['harga_jual_per_kg'] = (int) $data['harga_jual_per_kg'];
        $data['total_pendapatan'] = round($data['total_kg'] * $data['harga_jual_per_kg']);

        return $data;
    }

    private function createShipment(Request $request): Shipment
    {
        $data = $this->validatedShipment($request);

        return DB::transaction(function () use ($data) {
            $this->lockInventory();
            $this->ensureStockAvailable($data);
            $data['no_nota'] = Shipment::generateNoNota($data['tanggal']);

            return Shipment::create($data);
        });
    }

    private function lockInventory(): void
    {
        Purchase::whereIn('kategori', array_keys(Shipment::CARGO_FIELDS))
            ->orderBy('id')
            ->lockForUpdate()
            ->get(['id']);
    }

    private function ensureStockAvailable(array $shipmentData, ?Shipment $except = null): void
    {
        $stocks = Shipment::availableStock($except);

        foreach (Shipment::CARGO_FIELDS as $category => $field) {
            $requested = round((float) $shipmentData[$field], 2);
            $available = round((float) $stocks[$category], 2);

            if ($requested > 0 && $requested > $available) {
                throw ValidationException::withMessages([
                    $field => "Stok {$category} tidak mencukupi. Tersedia "
                        .number_format($available, 2, ',', '.').' Kg.',
                ]);
            }
        }
    }
}
