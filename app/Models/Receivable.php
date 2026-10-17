<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Receivable extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_transaksi',
        'tanggal',
        'pelanggan',
        'pemberi_pinjaman',
        'nominal_awal',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal_awal' => 'decimal:2',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(ReceivableTransaction::class);
    }

    public function getTotalTambahanAttribute(): float
    {
        if (array_key_exists('total_tambahan_sum', $this->attributes)) {
            return (float) $this->attributes['total_tambahan_sum'];
        }

        return (float) $this->transactions()
            ->where('jenis', 'loan')
            ->sum('nominal');
    }

    public function getTotalDibayarAttribute(): float
    {
        if (array_key_exists('total_dibayar_sum', $this->attributes)) {
            return (float) $this->attributes['total_dibayar_sum'];
        }

        return (float) $this->transactions()
            ->where('jenis', 'payment')
            ->sum('nominal');
    }

    public function getSisaUtangAttribute(): float
    {
        return (float) $this->nominal_awal
            + $this->total_tambahan
            - $this->total_dibayar;
    }

    public function getStatusAttribute(): string
    {
        if ($this->sisa_utang <= 0) {
            return 'Lunas';
        }

        return $this->total_dibayar > 0 ? 'Cicilan' : 'Belum Lunas';
    }
}
