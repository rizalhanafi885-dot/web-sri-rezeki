<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan Sumber Rezeki</title>
    <style>
        @page { margin: 28px 30px; }
        * { box-sizing: border-box; }
        body { color: #1e293b; font: 10px DejaVu Sans, sans-serif; }
        .header { border-bottom: 3px solid #15803d; margin-bottom: 16px; padding-bottom: 10px; }
        .brand { color: #15803d; font-size: 17px; font-weight: bold; }
        .subtitle { color: #64748b; font-size: 10px; margin-top: 4px; }
        .period { color: #334155; font-size: 11px; font-weight: bold; margin-top: 7px; }
        .section-title { background: #15803d; color: #fff; font-size: 11px; font-weight: bold; margin: 0 0 8px; padding: 7px 9px; }
        .cards { border-collapse: separate; border-spacing: 7px 0; margin: 0 -7px 14px; width: 100%; }
        .cards td { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 5px; padding: 8px; width: 25%; }
        .card-label { color: #475569; font-size: 8px; }
        .card-value { color: #166534; font-size: 12px; font-weight: bold; margin-top: 5px; }
        .columns { border-collapse: separate; border-spacing: 10px 0; margin: 0 -10px 14px; width: 100%; }
        .columns > tbody > tr > td { vertical-align: top; width: 50%; }
        table.data { border-collapse: collapse; width: 100%; }
        .data th { background: #f1f5f9; color: #334155; font-size: 9px; text-align: left; }
        .data th, .data td { border-bottom: 1px solid #e2e8f0; padding: 6px 7px; }
        .data td.amount, .data th.amount { text-align: right; white-space: nowrap; }
        .total td { background: #f0fdf4; color: #166534; font-weight: bold; }
        .profit td { background: #dcfce7; color: #14532d; font-weight: bold; }
        .note { background: #f8fafc; border-left: 3px solid #94a3b8; color: #64748b; line-height: 1.5; margin-top: 7px; padding: 7px; }
        .detail { page-break-before: auto; }
        .detail-table { font-size: 8px; }
        .detail-table th, .detail-table td { padding: 5px 4px; }
        .detail-table td { word-wrap: break-word; }
        .footer { border-top: 1px solid #cbd5e1; color: #64748b; font-size: 8px; margin-top: 14px; padding-top: 6px; text-align: right; }
    </style>
</head>
<body>
    <header class="header">
        <div class="brand">SUMBER REZEKI</div>
        <div class="subtitle">Laporan Keuangan Terperinci</div>
        <div class="period">Periode {{ \Carbon\Carbon::parse($report['from'])->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($report['to'])->format('d/m/Y') }}</div>
    </header>

    <table class="cards">
        <tr>
            <td><div class="card-label">PENDAPATAN PENJUALAN</div><div class="card-value">Rp {{ number_format($report['summary']['revenue'], 0, ',', '.') }}</div></td>
            <td><div class="card-label">TOTAL BEBAN</div><div class="card-value">Rp {{ number_format($report['summary']['total_expenses'], 0, ',', '.') }}</div></td>
            <td><div class="card-label">LABA / (RUGI) BERSIH</div><div class="card-value">Rp {{ number_format($report['summary']['net_profit'], 0, ',', '.') }}</div></td>
            <td><div class="card-label">TRANSAKSI TERINCI</div><div class="card-value">{{ number_format($report['summary']['transactions'], 0, ',', '.') }} transaksi</div></td>
        </tr>
    </table>

    <table class="columns">
        <tr>
            <td>
                <h2 class="section-title">LAPORAN LABA RUGI</h2>
                <table class="data">
                    <tbody>
                        <tr><td>Pendapatan penjualan PKS</td><td class="amount">Rp {{ number_format($report['summary']['revenue'], 0, ',', '.') }}</td></tr>
                        <tr><td>HPP pembelian sawit</td><td class="amount">(Rp {{ number_format($report['summary']['cost_of_goods'], 0, ',', '.') }})</td></tr>
                        <tr><td>Beban langsung lainnya</td><td class="amount">(Rp {{ number_format($report['summary']['additional_direct_expenses'], 0, ',', '.') }})</td></tr>
                        <tr class="total"><td>Total beban langsung</td><td class="amount">(Rp {{ number_format($report['summary']['direct_expenses'], 0, ',', '.') }})</td></tr>
                        <tr><td>Biaya operasional</td><td class="amount">(Rp {{ number_format($report['summary']['operational_expenses'], 0, ',', '.') }})</td></tr>
                        <tr class="profit"><td>Laba / (Rugi) bersih</td><td class="amount">Rp {{ number_format($report['summary']['net_profit'], 0, ',', '.') }}</td></tr>
                    </tbody>
                </table>
            </td>
            <td>
                <h2 class="section-title">NERACA PER {{ \Carbon\Carbon::parse($report['balance_sheet']['date'])->format('d/m/Y') }}</h2>
                <table class="data">
                    <tbody>
                        <tr><td>Kas dan bank</td><td class="amount">Rp {{ number_format($report['balance_sheet']['cash'], 0, ',', '.') }}</td></tr>
                        <tr><td>Persediaan (estimasi HPP)</td><td class="amount">Rp {{ number_format($report['balance_sheet']['inventory'], 0, ',', '.') }}</td></tr>
                        <tr><td>Piutang</td><td class="amount">Rp {{ number_format($report['balance_sheet']['receivables'], 0, ',', '.') }}</td></tr>
                        <tr class="total"><td>Total aset</td><td class="amount">Rp {{ number_format($report['balance_sheet']['total_assets'], 0, ',', '.') }}</td></tr>
                        <tr><td>Kewajiban tercatat</td><td class="amount">Rp {{ number_format($report['balance_sheet']['liabilities'], 0, ',', '.') }}</td></tr>
                        <tr class="profit"><td>Modal / saldo bersih</td><td class="amount">Rp {{ number_format($report['balance_sheet']['equity'], 0, ',', '.') }}</td></tr>
                    </tbody>
                </table>
                <div class="note">Nilai persediaan menggunakan estimasi harga beli rata-rata. Data kewajiban yang belum dicatat pada aplikasi tidak termasuk dalam neraca.</div>
            </td>
        </tr>
    </table>

    <section class="detail">
        <h2 class="section-title">RINCIAN TRANSAKSI PERIODE</h2>
        <table class="data detail-table">
            <thead>
                <tr>
                    <th>Tanggal</th><th>No. Referensi</th><th>Jenis</th><th>Kategori / Pihak</th>
                    <th>Keterangan</th><th class="amount">Jumlah (Rp)</th><th>Arus</th><th>Status / Akun</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report['rows'] as $row)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                        <td>{{ $row['number'] }}</td>
                        <td>{{ $row['type'] }}</td>
                        <td>{{ $row['category'] }}</td>
                        <td>{{ $row['description'] }}</td>
                        <td class="amount">{{ number_format($row['amount'], 0, ',', '.') }}</td>
                        <td>{{ $row['flow'] }}</td>
                        <td>{{ $row['status'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8">Tidak ada transaksi pada periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
    <footer class="footer">Dibuat {{ now()->format('d/m/Y H:i') }} · Sumber Rezeki</footer>
</body>
</html>
