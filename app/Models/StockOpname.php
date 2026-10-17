<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal',
        'kategori',
        'stok_sistem_kg',
        'stok_fisik_kg',
        'selisih_kg',
        'petugas',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'stok_sistem_kg' => 'decimal:2',
        'stok_fisik_kg' => 'decimal:2',
        'selisih_kg' => 'decimal:2',
    ];
}
