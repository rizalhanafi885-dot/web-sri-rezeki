<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\CashAccount;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PurchaseController extends Controller
{
    /**
     * Tampilkan data daftar Transaksi Pembelian (Petani)
     */
    public function index()
    {
        $purchases = Purchase::with('cashAccount:id,name')
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $nextNota = Purchase::generateNoNota();

        $cashAccounts = CashAccount::query()->whereNull('owner_id')->orderBy('name')->get();

        return view('admin.purchases.index', compact('purchases', 'nextNota', 'cashAccounts'));
    }

    /**
     * Simpan transaksi pembelian baru ke MySQL
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'cash_account_id' => ['nullable', 'integer', Rule::exists('cash_accounts', 'id')->whereNull('owner_id')],
            'petani' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string'],
            'harga_per_kg' => ['required', 'numeric', 'min:0'],
            'bruto' => ['required', 'numeric', 'min:0'],
            'tara' => ['required', 'numeric', 'min:0'],
            'netto_1' => ['required', 'numeric'],
            'rafaksi' => ['nullable', 'numeric', 'min:0', 'decimal:0,1'],
            'netto_2' => ['required', 'numeric'],
            'upah_bongkar' => ['nullable', 'integer', 'min:0', 'max:9999999999'],
            'total_pembelian' => ['required', 'numeric'],
            'total_bayar' => ['required', 'numeric'],
        ]);

        $validated['rafaksi'] = $validated['rafaksi'] ?? 0;
        $validated['upah_bongkar'] = $validated['upah_bongkar'] ?? 0;
        $validated['no_nota'] = Purchase::generateNoNota($validated['tanggal']);

        Purchase::create($validated);

        return redirect()->route('admin.purchases.index')->with('success', 'Transaksi pembelian ' . $validated['no_nota'] . ' berhasil disimpan!');
    }

    /**
     * Ambil data JSON transaksi untuk form modal Edit
     */
    public function showJson(Purchase $purchase)
    {
        return response()->json([
            'status' => 'success',
            'data' => $purchase
        ]);
    }

    /**
     * Update data transaksi pembelian di MySQL
     */
    public function update(Request $request, Purchase $purchase)
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'cash_account_id' => ['nullable', 'integer', Rule::exists('cash_accounts', 'id')->whereNull('owner_id')],
            'petani' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string'],
            'harga_per_kg' => ['required', 'numeric', 'min:0'],
            'bruto' => ['required', 'numeric', 'min:0'],
            'tara' => ['required', 'numeric', 'min:0'],
            'netto_1' => ['required', 'numeric'],
            'rafaksi' => ['nullable', 'numeric', 'min:0', 'decimal:0,1'],
            'netto_2' => ['required', 'numeric'],
            'upah_bongkar' => ['nullable', 'integer', 'min:0', 'max:9999999999'],
            'total_pembelian' => ['required', 'numeric'],
            'total_bayar' => ['required', 'numeric'],
        ]);

        $validated['rafaksi'] = $validated['rafaksi'] ?? 0;
        $validated['upah_bongkar'] = $validated['upah_bongkar'] ?? 0;

        $purchase->update($validated);

        return redirect()->route('admin.purchases.index')->with('success', 'Transaksi ' . $purchase->no_nota . ' berhasil diperbarui!');
    }

    /**
     * Tampilan Struk / Nota Timbangan untuk Print
     */
    public function print(Purchase $purchase)
    {
        return view('admin.purchases.print', compact('purchase'));
    }
}
