<?php

namespace App\Http\Controllers;

use App\Models\CashOutflow;
use App\Models\CashAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CashOutflowController extends Controller
{
    public function index()
    {
        return view('admin.cash-outflows.index', [
            'cashAccounts' => CashAccount::query()->whereNull('owner_id')->orderBy('name')->get(),
            'expenseCategories' => CashOutflow::EXPENSE_CATEGORIES,
            'outflows' => CashOutflow::query()
                ->with('cashAccount:id,name')
                ->orderByDesc('tanggal')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'cash_account_id' => ['nullable', 'integer', Rule::exists('cash_accounts', 'id')->whereNull('owner_id')],
            'kategori' => ['required', 'string', Rule::in(array_keys(CashOutflow::EXPENSE_CATEGORIES))],
            'nominal' => ['required', 'integer', 'min:1', 'max:999999999999'],
            'keterangan' => ['required', 'string', 'max:5000'],
        ]);

        $outflow = DB::transaction(function () use ($data) {
            $data['no_transaksi'] = $this->nextTransactionNumber($data['tanggal']);

            return CashOutflow::create($data);
        });

        return redirect()
            ->route('admin.cash-outflows.index')
            ->with('success', 'Pengeluaran '.$outflow->no_transaksi.' berhasil disimpan.');
    }

    public function update(Request $request, CashOutflow $cashOutflow)
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'cash_account_id' => ['nullable', 'integer', Rule::exists('cash_accounts', 'id')->whereNull('owner_id')],
            'kategori' => [
                'required',
                'string',
                Rule::in(array_unique(array_merge(
                    array_keys(CashOutflow::EXPENSE_CATEGORIES),
                    array_keys(CashOutflow::LEGACY_EXPENSE_CLASSIFICATIONS),
                    [$cashOutflow->kategori]
                ))),
            ],
            'nominal' => ['required', 'integer', 'min:1', 'max:999999999999'],
            'keterangan' => ['required', 'string', 'max:5000'],
        ]);

        $cashOutflow->update($data);

        return redirect()
            ->route('admin.cash-outflows.index')
            ->with('success', 'Pengeluaran '.$cashOutflow->no_transaksi.' berhasil diperbarui.');
    }

    public static function nextTransactionNumber(string $date): string
    {
        CashOutflow::query()->orderBy('id')->lockForUpdate()->get(['id']);
        $datePrefix = 'KK-'.\Carbon\Carbon::parse($date)->format('ymd').'-';
        $lastNumber = CashOutflow::where('no_transaksi', 'like', $datePrefix.'%')
            ->pluck('no_transaksi')
            ->map(fn (string $number) => (int) substr($number, strrpos($number, '-') + 1))
            ->max();

        return $datePrefix.str_pad((string) ((int) $lastNumber + 1), 3, '0', STR_PAD_LEFT);
    }
}
