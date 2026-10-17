<?php

namespace App\Http\Controllers;

use App\Models\CashOutflow;
use App\Models\CashAccount;
use App\Models\Receivable;
use App\Models\ReceivableTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReceivableController extends Controller
{
    public function index(Request $request)
    {
        $initialTotal = (float) Receivable::sum('nominal_awal');
        $loanTotal = (float) ReceivableTransaction::where('jenis', ReceivableTransaction::TYPE_LOAN)
            ->sum('nominal');
        $paidTotal = (float) ReceivableTransaction::where('jenis', ReceivableTransaction::TYPE_PAYMENT)
            ->sum('nominal');

        $query = Receivable::query()
            ->withSum([
                'transactions as total_tambahan_sum' => fn ($transactions) => $transactions
                    ->where('jenis', ReceivableTransaction::TYPE_LOAN),
            ], 'nominal')
            ->withSum([
                'transactions as total_dibayar_sum' => fn ($transactions) => $transactions
                    ->where('jenis', ReceivableTransaction::TYPE_PAYMENT),
            ], 'nominal')
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('pelanggan', 'like', '%'.$search.'%')
                    ->orWhere('no_transaksi', 'like', '%'.$search.'%');
            });
        }

        $receivables = $query->get();
        $statusFilter = $request->query('status');
        if (in_array($statusFilter, ['Belum Lunas', 'Cicilan', 'Lunas'], true)) {
            $receivables = $receivables
                ->filter(fn (Receivable $receivable) => $receivable->status === $statusFilter)
                ->values();
        }

        return view('admin.receivables.index', [
            'receivables' => $receivables,
            'initialTotal' => $initialTotal,
            'paidTotal' => $paidTotal,
            'balanceTotal' => $initialTotal + $loanTotal - $paidTotal,
            'filters' => $request->only('q', 'status'),
            'adminName' => $request->user()->name,
            'cashAccounts' => CashAccount::query()->whereNull('owner_id')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'pelanggan' => ['required', 'string', 'max:255'],
            'tanggal' => ['required', 'date'],
            'cash_account_id' => ['nullable', 'integer', Rule::exists('cash_accounts', 'id')->whereNull('owner_id')],
            'nominal_awal' => [
                'required',
                'numeric',
                'decimal:0,2',
                'min:0.01',
                'max:999999999999.99',
            ],
            'catatan' => ['nullable', 'string', 'max:5000'],
        ]);

        $cashAccountId = $data['cash_account_id'] ?? null;
        unset($data['cash_account_id']);

        $receivable = DB::transaction(function () use ($data, $request, $cashAccountId) {
            Receivable::query()->orderBy('id')->lockForUpdate()->get(['id', 'tanggal']);
            $datePrefix = 'PTG-'.\Carbon\Carbon::parse($data['tanggal'])->format('ymd').'-';
            $lastNumber = Receivable::where('no_transaksi', 'like', $datePrefix.'%')
                ->pluck('no_transaksi')
                ->map(fn (string $number) => (int) substr($number, strrpos($number, '-') + 1))
                ->max();

            $data['no_transaksi'] = $datePrefix.str_pad((string) ((int) $lastNumber + 1), 3, '0', STR_PAD_LEFT);
            $data['pemberi_pinjaman'] = $request->user()->name;

            $receivable = Receivable::create($data);
            CashOutflow::create([
                'no_transaksi' => CashOutflowController::nextTransactionNumber($data['tanggal']),
                'tanggal' => $data['tanggal'],
                'cash_account_id' => $cashAccountId,
                'kategori' => 'Pencairan Piutang - '.$data['pelanggan'],
                'nominal' => $data['nominal_awal'],
                'keterangan' => 'Pencairan piutang awal '.$receivable->no_transaksi.' oleh '.$request->user()->name,
            ]);

            return $receivable;
        });

        return redirect()
            ->route('admin.receivables.index')
            ->with('success', 'Piutang '.$receivable->no_transaksi.' berhasil disimpan.');
    }

    public function showJson(Receivable $receivable)
    {
        $receivable->load([
            'transactions' => fn ($query) => $query
                ->orderBy('tanggal')
                ->orderBy('id'),
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $receivable->id,
                'no_transaksi' => $receivable->no_transaksi,
                'tanggal' => Carbon::parse($receivable->tanggal)->format('Y-m-d'),
                'pelanggan' => $receivable->pelanggan,
                'pemberi_pinjaman' => $receivable->pemberi_pinjaman,
                'nominal_awal' => (float) $receivable->nominal_awal,
                'total_tambahan' => $receivable->total_tambahan,
                'total_dibayar' => $receivable->total_dibayar,
                'sisa_utang' => $receivable->sisa_utang,
                'status_label' => $receivable->status,
                'catatan' => $receivable->catatan,
                'transactions' => $receivable->transactions->map(fn (ReceivableTransaction $transaction) => [
                    'no_pembayaran' => $transaction->no_pembayaran,
                    'tanggal' => Carbon::parse($transaction->tanggal)->format('d M Y'),
                    'jenis' => $transaction->jenis,
                    'nominal' => (float) $transaction->nominal,
                    'metode' => $transaction->metode,
                    'admin_penerima' => $transaction->admin_penerima,
                    'catatan' => $transaction->catatan,
                ]),
            ],
        ]);
    }

    public function addLoan(Request $request, Receivable $receivable)
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'cash_account_id' => ['nullable', 'integer', Rule::exists('cash_accounts', 'id')->whereNull('owner_id')],
            'nominal' => [
                'required',
                'numeric',
                'decimal:0,2',
                'min:0.01',
                'max:999999999999.99',
            ],
            'catatan' => ['nullable', 'string', 'max:5000'],
        ]);

        DB::transaction(function () use ($data, $receivable, $request) {
            $lockedReceivable = Receivable::whereKey($receivable->id)
                ->lockForUpdate()
                ->firstOrFail();

            $transaction = $lockedReceivable->transactions()->create([
                'no_pembayaran' => $this->nextTransactionNumber($lockedReceivable, 'T'),
                'tanggal' => $data['tanggal'],
                'cash_account_id' => $data['cash_account_id'] ?? null,
                'jenis' => ReceivableTransaction::TYPE_LOAN,
                'nominal' => $data['nominal'],
                'metode' => 'Pinjaman Tambahan',
                'admin_penerima' => $request->user()->name,
                'catatan' => $data['catatan'] ?? null,
            ]);

            CashOutflow::create([
                'no_transaksi' => CashOutflowController::nextTransactionNumber($data['tanggal']),
                'tanggal' => $data['tanggal'],
                'cash_account_id' => $data['cash_account_id'] ?? null,
                'kategori' => 'Pencairan Piutang - '.$lockedReceivable->pelanggan,
                'nominal' => $data['nominal'],
                'keterangan' => 'Pencairan pinjaman tambahan '.$transaction->no_pembayaran
                    .' untuk '.$lockedReceivable->no_transaksi.' oleh '.$request->user()->name,
            ]);
        });

        return redirect()
            ->route('admin.receivables.index')
            ->with('success', 'Pinjaman tambahan berhasil dicatat.');
    }

    public function pay(Request $request, Receivable $receivable)
    {
        $validator = Validator::make($request->all(), [
            'tanggal' => ['required', 'date'],
            'cash_account_id' => ['nullable', 'integer', Rule::exists('cash_accounts', 'id')->whereNull('owner_id')],
            'nominal' => [
                'required',
                'numeric',
                'decimal:0,2',
                'min:0.01',
                'max:999999999999.99',
            ],
            'metode' => ['required', Rule::in(['Tunai', 'Transfer', 'Lainnya'])],
            'catatan' => ['nullable', 'string', 'max:5000'],
        ]);

        $data = $validator->validate();
        $payment = (float) $data['nominal'];

        DB::transaction(function () use ($data, $receivable, $request, $payment) {
            $lockedReceivable = Receivable::whereKey($receivable->id)
                ->lockForUpdate()
                ->firstOrFail();

            $balance = $lockedReceivable->sisa_utang;
            if ($payment > $balance) {
                throw ValidationException::withMessages([
                    'nominal' => 'Nominal cicilan melebihi sisa utang sebesar Rp '
                        .number_format($balance, 0, ',', '.').'.',
                ]);
            }

            $lockedReceivable->transactions()->create([
                'no_pembayaran' => $this->nextTransactionNumber($lockedReceivable, 'C'),
                'tanggal' => $data['tanggal'],
                'cash_account_id' => $data['cash_account_id'] ?? null,
                'jenis' => ReceivableTransaction::TYPE_PAYMENT,
                'nominal' => $payment,
                'metode' => $data['metode'],
                'admin_penerima' => $request->user()->name,
                'catatan' => $data['catatan'] ?? null,
            ]);
        });

        return redirect()
            ->route('admin.receivables.index')
            ->with('success', 'Pembayaran cicilan berhasil disimpan.');
    }

    private function nextTransactionNumber(Receivable $receivable, string $type): string
    {
        $prefix = $receivable->no_transaksi.'-'.$type;
        $lastNumber = $receivable->transactions()
            ->where('no_pembayaran', 'like', $prefix.'%')
            ->pluck('no_pembayaran')
            ->map(fn (string $number) => (int) substr($number, strrpos($number, $type) + 1))
            ->max();

        return $prefix.str_pad((string) ((int) $lastNumber + 1), 3, '0', STR_PAD_LEFT);
    }
}
