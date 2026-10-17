<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\HasCashAccount;

class Purchase extends Model
{
    use HasFactory;
    use HasCashAccount;

    protected $fillable = [
        'no_nota',
        'cash_account_id',
        'tanggal',
        'petani',
        'kategori',
        'harga_per_kg',
        'bruto',
        'tara',
        'netto_1',
        'rafaksi',
        'netto_2',
        'upah_bongkar',
        'total_pembelian',
        'total_bayar',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'harga_per_kg' => 'float',
        'bruto' => 'float',
        'tara' => 'float',
        'netto_1' => 'float',
        'rafaksi' => 'float',
        'netto_2' => 'float',
        'upah_bongkar' => 'float',
        'total_pembelian' => 'float',
        'total_bayar' => 'float',
    ];

    /**
     * Generate nomor nota berdasarkan tanggal.
     * Format: BL-261014-001
     */
    public static function generateNoNota($date = null): string
    {
        $d = $date ? Carbon::parse($date) : now();
        $prefix = 'BL-' . $d->format('ymd') . '-';

        $lastNumber = self::where('no_nota', 'like', $prefix . '%')
            ->pluck('no_nota')
            ->map(fn (string $noNota) => (int) substr($noNota, strrpos($noNota, '-') + 1))
            ->max();

        $nextNumber = ((int) $lastNumber) + 1;

        return $prefix . str_pad(
            (string) $nextNumber,
            3,
            '0',
            STR_PAD_LEFT
        );
    }

    public function cashAccount(): BelongsTo
    {
        return $this->belongsTo(CashAccount::class);
    }
}