<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\HasCashAccount;

class ReceivableTransaction extends Model
{
    use HasFactory;
    use HasCashAccount;

    public const TYPE_LOAN = 'loan';

    public const TYPE_PAYMENT = 'payment';

    protected $fillable = [
        'receivable_id',
        'cash_account_id',
        'no_pembayaran',
        'tanggal',
        'jenis',
        'nominal',
        'metode',
        'admin_penerima',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2',
    ];

    public function receivable(): BelongsTo
    {
        return $this->belongsTo(Receivable::class);
    }

    public function cashAccount(): BelongsTo
    {
        return $this->belongsTo(CashAccount::class);
    }
}
