<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\HasCashAccount;

class CashOutflow extends Model
{
    use HasFactory;
    use HasCashAccount;

    public const EXPENSE_CATEGORIES = [
        'Beban Fee Pembelian barang' => 'direct',
        'Upah Bongkar Barang' => 'direct',
        'Upah Muat Barang' => 'direct',
        'Ongkos Armada' => 'direct',
        'Listrik' => 'operational',
        'Kuota Internet' => 'operational',
        'CCTV' => 'operational',
        'Gaji Karyawan' => 'operational',
        'Biaya Operasional Lainnya' => 'operational',
    ];

    public const LEGACY_EXPENSE_CLASSIFICATIONS = [
        'OP' => 'operational',
        'Operasional' => 'operational',
        'Upah Bongkar' => 'direct',
        'Upah Muat' => 'direct',
        'Biaya Operasional (Listrik, Kuota CCTV, Gaji Karyawan dll)' => 'operational',
    ];

    protected $fillable = [
        'no_transaksi',
        'cash_account_id',
        'tanggal',
        'kategori',
        'nominal',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2',
    ];

    public function cashAccount(): BelongsTo
    {
        return $this->belongsTo(CashAccount::class);
    }
}
