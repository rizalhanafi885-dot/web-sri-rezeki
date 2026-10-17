@extends('layouts.admin')

@section('title', 'Pusat Laporan - Sumber Rezeki')

@section('content')
<style>
    .main-wrapper { min-width: 0; }
    .report-heading { margin: 0 0 18px; font-size: 20px; }
    .report-filter-card, .report-results-card { margin-bottom: 16px; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; box-shadow: 0 1px 3px rgba(0, 0, 0, .05); }
    .report-filter-card { padding: 14px; }
    .report-filter { display: grid; grid-template-columns: repeat(3, minmax(140px, 1fr)) minmax(120px, .65fr); align-items: end; gap: 12px; }
    .report-form-group label { display: block; margin-bottom: 5px; font-size: 13px; }
    .report-control { width: 100%; min-height: 38px; border: 1px solid #cbd5e1; border-radius: 5px; background: #fff; padding: 8px 10px; font: inherit; font-size: 13px; }
    .report-btn { min-height: 38px; border: 0; border-radius: 5px; padding: 8px 14px; color: #fff; background: #198754; font: inherit; font-size: 13px; font-weight: 600; white-space: nowrap; cursor: pointer; }
    .report-btn:hover { filter: brightness(.95); }
    .report-results-card { padding: 12px; }
    .report-table-wrap { max-width: 100%; overflow-x: auto; }
    .report-table { width: 100%; border-collapse: collapse; font-size: 13px; text-align: left; }
    .report-table th, .report-table td { padding: 10px 9px; border-bottom: 1px solid #dbe2ea; white-space: nowrap; }
    .report-table thead th { background: #f8fafc; font-weight: 700; }
    .report-table tbody tr:hover { background: #f8fafc; }
    .report-number { color: #198754; font-weight: 700; }
    .report-outflow { color: #dc3545; font-weight: 700; }
    .report-summary { display: flex; flex-wrap: wrap; gap: 18px; margin: 0 0 12px; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 5px; background: #f8fafc; font-size: 13px; }
    .report-empty, .report-prompt { color: #64748b; }
    .report-prompt { padding: 10px 8px; font-size: 13px; }
    .report-alert { margin-bottom: 14px; border-radius: 6px; padding: 12px 14px; color: #991b1b; background: #fee2e2; }
    .report-download { display: flex; flex-wrap: wrap; align-items: end; gap: 10px; margin-bottom: 14px; padding: 12px; border: 1px solid #bbf7d0; border-radius: 6px; background: #f0fdf4; }
    .report-format-label { display: flex; min-height: 38px; align-items: center; gap: 8px; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 5px; background: #fff; color: #334155; font-size: 13px; font-weight: 600; }
    .report-format-label i { color: #dc3545; }
    .report-download .report-control { min-width: 190px; }
    @media (max-width: 800px) { .report-filter { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 520px) { .report-filter { grid-template-columns: 1fr; } }
</style>

<div class="container-fluid">
    <h1 class="report-heading">Pusat Laporan</h1>

    @if($errors->any())
        <div class="report-alert">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <section class="report-filter-card" aria-label="Filter laporan">
        <form class="report-filter" method="GET" action="{{ route('admin.reports.index') }}">
            <div class="report-form-group">
                <label for="reportFrom">Dari Tanggal</label>
                <input class="report-control" id="reportFrom" type="date" name="from"
                    value="{{ $filters['from'] ?? request('from') }}" required>
            </div>
            <div class="report-form-group">
                <label for="reportTo">Sampai Tanggal</label>
                <input class="report-control" id="reportTo" type="date" name="to"
                    value="{{ $filters['to'] ?? request('to') }}" required>
            </div>
            <div class="report-form-group">
                <label for="reportType">Jenis Laporan</label>
                <select class="report-control" id="reportType" name="type" required>
                    @foreach($reportTypes as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['type'] ?? request('type', 'purchases')) === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button class="report-btn" type="submit"><i class="fa-solid fa-file-circle-check"></i> Buat Laporan</button>
        </form>
    </section>

    <section class="report-results-card" aria-label="Hasil laporan">
        @if(!$hasFilters)
            <div class="report-prompt"><strong>Silakan filter laporan terlebih dahulu.</strong></div>
        @else
            <form class="report-download" method="GET" action="{{ route('admin.reports.export') }}">
                <input type="hidden" name="from" value="{{ $filters['from'] }}">
                <input type="hidden" name="to" value="{{ $filters['to'] }}">
                <input type="hidden" name="type" value="{{ $filters['type'] }}">
                <div class="report-format-label">
                    <i class="fa-solid fa-file-pdf"></i> Format unduhan: PDF
                </div>
                <button class="report-btn" type="submit"><i class="fa-solid fa-download"></i> Unduh Laporan</button>
            </form>
        @endif

        @if($hasFilters && $filters['type'] === 'all')
            @foreach($sections as $section)
                <h2 class="report-heading">{{ $section['title'] }}</h2>
                <div class="report-summary">
                    @foreach($section['summary'] as $label => $value)
                        <strong>{{ $label }}: {{ $value }}</strong>
                    @endforeach
                </div>
                <div class="report-table-wrap" style="margin-bottom: 22px;">
                    <table class="report-table">
                        <thead>
                            <tr>
                                @foreach($section['columns'] as $column)
                                    <th>{{ $column }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($section['rows'] as $row)
                                <tr>
                                    @foreach($row as $cell)
                                        <td>{{ $cell }}</td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr><td colspan="{{ count($section['columns']) }}" class="report-empty">Tidak ada transaksi pada rentang tanggal ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endforeach
        @elseif($hasFilters && $filters['type'] === 'purchases')
            <div class="report-summary">
                <strong>Jumlah transaksi: {{ number_format($summary['transactions'], 0, ',', '.') }}</strong>
                <strong>Total dibayarkan: Rp {{ number_format($summary['total'], 0, ',', '.') }}</strong>
            </div>
            <div class="report-table-wrap">
                <table class="report-table">
                    <thead><tr><th>No. Nota</th><th>Tanggal</th><th>Petani</th><th>Kategori</th><th>Netto 2 (Kg)</th><th>Total Bayar (Rp)</th></tr></thead>
                    <tbody>
                        @forelse($rows as $purchase)
                            <tr>
                                <td>{{ $purchase->no_nota }}</td>
                                <td>{{ $purchase->tanggal->format('d/m/Y') }}</td>
                                <td>{{ $purchase->petani }}</td>
                                <td>{{ $purchase->kategori }}</td>
                                <td>{{ number_format($purchase->netto_2, 2, ',', '.') }}</td>
                                <td class="report-number">Rp {{ number_format($purchase->total_bayar, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="report-empty">Tidak ada transaksi pembelian pada rentang tanggal ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @elseif($hasFilters && $filters['type'] === 'shipments')
            <div class="report-summary">
                <strong>Jumlah pengiriman: {{ number_format($summary['transactions'], 0, ',', '.') }}</strong>
                <strong>Total muatan: {{ number_format($summary['total_kg'], 2, ',', '.') }} Kg</strong>
            </div>
            <div class="report-table-wrap">
                <table class="report-table">
                    <thead><tr><th>No. Surat Jalan</th><th>Tanggal</th><th>PKS Tujuan</th><th>Nopol</th><th>Driver</th><th>Total (Kg)</th></tr></thead>
                    <tbody>
                        @forelse($rows as $shipment)
                            <tr>
                                <td>{{ $shipment->no_nota }}</td>
                                <td>{{ $shipment->tanggal->format('d/m/Y') }}</td>
                                <td>{{ $shipment->pks_tujuan }}</td>
                                <td>{{ $shipment->nopol }}</td>
                                <td>{{ $shipment->driver }}</td>
                                <td>{{ number_format($shipment->total_kg, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="report-empty">Tidak ada pengiriman pada rentang tanggal ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @elseif($hasFilters && $filters['type'] === 'profit-loss')
            <div class="report-summary">
                <strong>Pendapatan penjualan PKS: <span class="report-number">Rp {{ number_format($summary['revenue'], 0, ',', '.') }}</span></strong>
                <strong>HPP pembelian sawit: <span class="report-outflow">Rp {{ number_format($summary['cost_of_goods'], 0, ',', '.') }}</span></strong>
                <strong>Beban langsung kategori 1–4: <span class="report-outflow">Rp {{ number_format($summary['additional_direct_expenses'], 0, ',', '.') }}</span></strong>
                <strong>Total beban biaya langsung: <span class="report-outflow">Rp {{ number_format($summary['direct_expenses'], 0, ',', '.') }}</span></strong>
                <strong>Biaya operasional kategori 5: <span class="report-outflow">Rp {{ number_format($summary['operational_expenses'], 0, ',', '.') }}</span></strong>
                <strong>Total beban: <span class="report-outflow">Rp {{ number_format($summary['total_expenses'], 0, ',', '.') }}</span></strong>
                <strong>{{ $summary['net_profit'] < 0 ? 'Rugi bersih' : 'Laba bersih' }}:
                    <span class="{{ $summary['net_profit'] < 0 ? 'report-outflow' : 'report-number' }}">
                        Rp {{ number_format(abs($summary['net_profit']), 0, ',', '.') }}
                    </span>
                </strong>
            </div>
            <p class="report-prompt">
                HPP memakai nilai pembelian sawit dalam periode. Pendapatan pengiriman lama yang belum memiliki harga jual tercatat dihitung Rp 0; edit pengiriman tersebut untuk melengkapi harga.
            </p>
            <div class="report-table-wrap">
                <table class="report-table">
                    <thead><tr><th>No. Transaksi</th><th>Tanggal</th><th>Komponen</th><th>Kategori / Tujuan</th><th>Keterangan</th><th>Nominal (Rp)</th></tr></thead>
                    <tbody>
                        @forelse($rows as $profitLossRow)
                            <tr>
                                <td>{{ $profitLossRow['number'] }}</td>
                                <td>{{ $profitLossRow['date']->format('d/m/Y') }}</td>
                                <td>{{ $profitLossRow['type'] }}</td>
                                <td>{{ $profitLossRow['category'] }}</td>
                                <td>{{ $profitLossRow['description'] }}</td>
                                <td class="{{ $profitLossRow['direction'] === 'in' ? 'report-number' : 'report-outflow' }}">
                                    {{ $profitLossRow['direction'] === 'in' ? '+' : '-' }} Rp {{ number_format($profitLossRow['amount'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="report-empty">Tidak ada pendapatan atau beban pada rentang tanggal ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @elseif($hasFilters)
            <div class="report-summary">
                <strong>Total kas masuk: <span class="report-number">Rp {{ number_format($summary['total_in'], 0, ',', '.') }}</span></strong>
                <strong>Total kas keluar: <span class="report-outflow">Rp {{ number_format($summary['total_out'], 0, ',', '.') }}</span></strong>
                <strong>Saldo bersih: Rp {{ number_format($summary['net'], 0, ',', '.') }}</strong>
            </div>
            <div class="report-table-wrap">
                <table class="report-table">
                    <thead><tr><th>No. Transaksi</th><th>Tanggal</th><th>Jenis</th><th>Kategori / Sumber</th><th>Keterangan</th><th>Nominal (Rp)</th></tr></thead>
                    <tbody>
                        @forelse($rows as $cashTransaction)
                            <tr>
                                <td>{{ $cashTransaction['number'] }}</td>
                                <td>{{ $cashTransaction['date']->format('d/m/Y') }}</td>
                                <td>{{ $cashTransaction['direction'] === 'in' ? 'Kas Masuk' : 'Kas Keluar' }}</td>
                                <td>{{ $cashTransaction['category'] }}</td>
                                <td>{{ $cashTransaction['description'] }}</td>
                                <td class="{{ $cashTransaction['direction'] === 'in' ? 'report-number' : 'report-outflow' }}">
                                    {{ $cashTransaction['direction'] === 'in' ? '+' : '-' }} Rp {{ number_format($cashTransaction['amount'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="report-empty">Tidak ada transaksi kas pada rentang tanggal ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
@endsection
