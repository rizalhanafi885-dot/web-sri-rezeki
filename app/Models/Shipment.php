<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    use HasFactory;

    public const CARGO_FIELDS = [
        'TBS' => 'tbs_kg',
        'Brondolan' => 'brondolan_kg',
        'BRD Afkir' => 'brd_afkir_kg',
        'Buah Kecil' => 'buah_kecil_kg',
    ];

    protected $fillable = [
        'no_nota',
        'cash_account_id',
        'tanggal',
        'pks_tujuan',
        'nopol',
        'driver',
        'tbs_kg',
        'brondolan_kg',
        'brd_afkir_kg',
        'buah_kecil_kg',
        'total_kg',
        'harga_jual_per_kg',
        'total_pendapatan',
        'is_paid',
        'paid_at',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total_kg' => 'decimal:2',
        'tbs_kg' => 'decimal:2',
        'brondolan_kg' => 'decimal:2',
        'brd_afkir_kg' => 'decimal:2',
        'buah_kecil_kg' => 'decimal:2',
        'harga_jual_per_kg' => 'decimal:2',
        'total_pendapatan' => 'decimal:2',
        'is_paid' => 'boolean',
        'paid_at' => 'date',
    ];

    public static function generateNoNota($date = null): string
    {
        $date = $date
            ? \Carbon\Carbon::parse($date)
            : now();

        $prefix = 'SJ-'.$date->format('ymd').'-';

        $last = self::where('no_nota', 'like', $prefix.'%')
            ->pluck('no_nota')
            ->map(fn (string $noNota) => (int) substr($noNota, strrpos($noNota, '-') + 1))
            ->max();

        return $prefix.str_pad(
            (string) ((int) $last + 1),
            3,
            '0',
            STR_PAD_LEFT
        );
    }

    public static function availableStock(?self $except = null, ?StockOpname $exceptOpname = null): array
    {
        $purchased = Purchase::query()
            ->selectRaw('kategori, SUM(netto_1) as total_kg')
            ->whereIn('kategori', array_keys(self::CARGO_FIELDS))
            ->groupBy('kategori')
            ->pluck('total_kg', 'kategori');

        $shippedQuery = self::query();
        if ($except) {
            $shippedQuery->where('id', '!=', $except->id);
        }

        $shipped = $shippedQuery
            ->selectRaw(implode(', ', array_map(
                fn (string $field) => "COALESCE(SUM({$field}), 0) as {$field}",
                array_values(self::CARGO_FIELDS)
            )))
            ->first();

        $adjustmentQuery = StockOpname::query()
            ->selectRaw('kategori, SUM(selisih_kg) as total_selisih')
            ->whereIn('kategori', array_keys(self::CARGO_FIELDS))
            ->groupBy('kategori');
        if ($exceptOpname) {
            $adjustmentQuery->where('id', '!=', $exceptOpname->id);
        }
        $adjustments = $adjustmentQuery->pluck('total_selisih', 'kategori');

        $stocks = [];
        foreach (self::CARGO_FIELDS as $category => $field) {
            $stocks[$category] = round(
                (float) ($purchased[$category] ?? 0)
                    - (float) $shipped->{$field}
                    + (float) ($adjustments[$category] ?? 0),
                2
            );
        }

        return $stocks;
    }

    public function cashAccount(): BelongsTo
    {
        return $this->belongsTo(CashAccount::class);
    }
}
