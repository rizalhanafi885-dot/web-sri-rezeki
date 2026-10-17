<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'type',
        'initial_balance',
    ];

    protected $casts = [
        'initial_balance' => 'decimal:2',
    ];

    public function inflows(): HasMany
    {
        return $this->hasMany(CashInflow::class);
    }

    public function outflows(): HasMany
    {
        return $this->hasMany(CashOutflow::class);
    }

    public function getCurrentBalanceAttribute(): float
    {
        return (float) $this->initial_balance
            + (float) $this->inflows()->sum('nominal')
            + (float) $this->shipments()->where('is_paid', true)->sum('total_pendapatan')
            + (float) $this->receivableTransactions()->where('jenis', ReceivableTransaction::TYPE_PAYMENT)->sum('nominal')
            - (float) $this->outflows()->sum('nominal')
            - (float) $this->purchases()->sum('total_bayar')
            + (float) $this->transfersIn()->sum('nominal')
            - (float) $this->transfersOut()->sum('nominal');
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function receivableTransactions(): HasMany
    {
        return $this->hasMany(ReceivableTransaction::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function transfersIn(): HasMany
    {
        return $this->hasMany(CashTransfer::class, 'destination_account_id');
    }

    public function transfersOut(): HasMany
    {
        return $this->hasMany(CashTransfer::class, 'source_account_id');
    }
}
