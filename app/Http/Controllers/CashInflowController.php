<?php

namespace App\Http\Controllers;

use App\Models\CashInflow;
use App\Models\CashAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CashInflowController extends Controller
{
    public function index()
    {
        return view('admin.cash-inflows.index', [
            'cashAccounts' => CashAccount::query()->whereNull('owner_id')->orderBy('name')->get(),
            'inflows' => CashInflow::query()
                ->with('cashAccount:id,name')
                ->where(function ($query) {
                    $query->whereNull('cash_account_id')
                        ->orWhereHas('cashAccount', fn ($query) => $query->whereNull('owner_id'));
                })
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
            'sumber' => ['required', 'string', 'max:255'],
            'nominal' => ['required', 'integer', 'min:1', 'max:999999999999'],
            'keterangan' => ['required', 'string', 'max:5000'],
        ]);

        $inflow = DB::transaction(function () use ($data) {
            $data['no_transaksi'] = self::nextTransactionNumber($data['tanggal']);

            return CashInflow::create($data);
        });

        return redirect()
            ->route('admin.cash-inflows.index')
            ->with('success', 'Pemasukan '.$inflow->no_transaksi.' berhasil disimpan.');
    }

    public function update(Request $request, CashInflow $cashInflow)
    {
        abort_unless(
            $cashInflow->cash_account_id === null || $cashInflow->cashAccount()->whereNull('owner_id')->exists(),
            404
        );

        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'cash_account_id' => ['nullable', 'integer', Rule::exists('cash_accounts', 'id')->whereNull('owner_id')],
            'sumber' => ['required', 'string', 'max:255'],
            'nominal' => ['required', 'integer', 'min:1', 'max:999999999999'],
            'keterangan' => ['required', 'string', 'max:5000'],
        ]);

        $cashInflow->update($data);

        return redirect()
            ->route('admin.cash-inflows.index')
            ->with('success', 'Pemasukan '.$cashInflow->no_transaksi.' berhasil diperbarui.');
    }

    public static function nextTransactionNumber(string $date): string
    {
        CashInflow::query()->orderBy('id')->lockForUpdate()->get(['id', 'tanggal']);
        $datePrefix = 'KM-'.\Carbon\Carbon::parse($date)->format('ymd').'-';
        $lastNumber = CashInflow::where('no_transaksi', 'like', $datePrefix.'%')
            ->pluck('no_transaksi')
            ->map(fn (string $number) => (int) substr($number, strrpos($number, '-') + 1))
            ->max();

        return $datePrefix.str_pad((string) ((int) $lastNumber + 1), 3, '0', STR_PAD_LEFT);
    }
}
