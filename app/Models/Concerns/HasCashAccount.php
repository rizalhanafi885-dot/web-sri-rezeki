<?php

namespace App\Models\Concerns;

use App\Models\CashAccount;

trait HasCashAccount
{
    protected static function bootHasCashAccount(): void
    {
        static::creating(function ($model) {
            if (empty($model->cash_account_id)) {
                $model->cash_account_id = CashAccount::query()
                    ->whereNull('owner_id')
                    ->where('type', 'cash')
                    ->value('id');
            }
        });
    }
}
