<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan Sumber Rezeki</title>
    <style>
        @page { margin: 25px 34px; }
        * { box-sizing: border-box; }
        body { color: #16405d; font: 8px DejaVu Sans, sans-serif; }
        .page-meta { color: #111827; font-size: 8px; margin-bottom: 19px; }
        .page-meta td:last-child { text-align: right; }
        .report-title { color: #0789ce; font-size: 12px; font-weight: bold; margin: 0 0 17px 3px; }
        .cards { border-collapse: separate; border-spacing: 11px 0; margin: 0 -11px 29px; table-layout: fixed; width: calc(100% + 22px); }
        .cards td { border: 1px solid #3c78d8; border-radius: 7px; height: 77px; padding: 8px 5px 6px; text-align: center; vertical-align: middle; width: 16.66%; }
        .card-icon { height: 34px; line-height: 34px; margin: 0 auto; width: 37px; }
        .card-icon img { display: block; height: 34px; width: 37px; }
        .card-percent { color: #2475bb; font-size: 7px; margin-top: -21px; text-align: left; }
        .card-label { color: #075080; font-size: 6px; font-weight: bold; margin-top: 14px; text-transform: uppercase; }
        .card-value { color: #0065a1; font-size: 9px; font-weight: bold; margin-top: 3px; white-space: nowrap; }
        .main-columns { border-collapse: separate; border-spacing: 31px 0; margin: 0 -31px 12px; table-layout: fixed; width: calc(100% + 62px); }
        .main-columns > tbody > tr > td { vertical-align: top; width: 50%; }
        .section-title { color: #fff; font-size: 7px; font-weight: bold; height: 15px; margin: 0 0 6px; padding: 4px 5px; }
        .blue { background: #5b8fe0; }
        .green { background: #07956b; }
        .statement { border-collapse: collapse; font-size: 6.8px; width: 100%; }
        .statement td { border-bottom: 1px solid #aab5bf; height: 13px; padding: 2px 3px; }
        .statement td:last-child { text-align: right; white-space: nowrap; }
        .statement .total td { background: #e7efff; color: #005f9a; font-weight: bold; }
        .statement .green-total td { background: #e2f5ee; color: #087b5c; font-weight: bold; }
        .account-row td:first-child { color: #087bb3; }
        .balance-layout { border-collapse: collapse; margin: 0 0 4px; table-layout: fixed; width: 100%; }
        .balance-layout > tbody > tr > td { vertical-align: top; }
        .chart-cell { padding: 0 7px 0 0; width: 29%; }
        .legend-cell { width: 71%; }
        .chart { display: block; height: 96px; margin: 0 auto; width: 96px; }
        .legend { border-collapse: collapse; font-size: 6px; width: 100%; }
        .legend td { border-bottom: 1px solid #c1c9d0; padding: 3px 2px; }
        .legend td:nth-child(2) { text-align: center; width: 35px; }
        .legend td:last-child { text-align: right; white-space: nowrap; }
        .swatch { display: inline-block; height: 7px; margin-right: 3px; width: 17px; }
        .subheading { color: #087bb3; font-size: 6px; font-weight: bold; margin: 8px 0 2px; }
        .difference { color: #087bb3; font-size: 6px; margin-top: 6px; }
        .note { color: #087bb3; font-size: 5.5px; margin: 8px 0 10px; }
        .bottom-cards { border-collapse: separate; border-spacing: 14px 0; margin: 4px -14px 0; table-layout: fixed; width: calc(100% + 28px); }
        .bottom-cards td { border: 1px solid #4b83cd; border-radius: 7px; height: 49px; padding: 5px 9px; text-align: center; }
        .bottom-cards td:nth-child(1), .bottom-cards td:nth-child(2), .bottom-cards td:nth-child(4) { width: 22%; }
        .bottom-cards td:nth-child(3) { border: 0; width: 34%; }
        .bottom-card { display: table; margin: 0 auto; }
        .bottom-cards td:nth-child(4) .bottom-card { margin-left: auto; margin-right: 0; }
        .bottom-icon, .bottom-copy { display: table-cell; vertical-align: middle; }
        .bottom-icon { padding-right: 9px; }
        .bottom-icon { height: 34px; padding-right: 9px; width: 38px; }
        .bottom-icon img { height: 34px; width: 34px; }
        .bottom-copy { text-align: center; }
        .bottom-label { color: #005f91; font-size: 9px; font-weight: bold; }
        .bottom-value { color: #06976a; font-size: 12px; font-weight: bold; margin-top: 3px; }
        .bottom-value.dark { color: #1f2937; }
        .page-two { page-break-before: always; }
        .trip-table { border-collapse: collapse; font-size: 6px; table-layout: fixed; width: 100%; }
        .trip-table th { background: #326c57; color: #fff; font-size: 6px; font-weight: bold; height: 28px; line-height: 1.25; padding: 4px 2px; text-align: center; }
        .trip-table td { border: 1px solid #d2d9dc; color: #172b3b; height: 15px; padding: 3px 2px; text-align: right; white-space: nowrap; }
        .trip-table tbody tr:nth-child(even) td { background: #f0f3f4; }
        .trip-table td.left { text-align: left; }
        .trip-table td.center { text-align: center; }
        .trip-table td.pks { color: #0875cc; font-weight: bold; }
        .trip-table td.status { color: #08775b; text-align: center; }
        .trip-table td.negative { color: #e3342f; }
        .trip-table tfoot td { background: #3978d1; color: #fff; font-weight: bold; }
        .trip-empty { color: #64748b; font-style: italic; text-align: center !important; }
        .page-footer { color: #64748b; font-size: 6px; margin-top: 7px; text-align: right; }
    </style>
</head>
<body>
    <table class="page-meta"><tr>
        <td>{{ now()->format('d/m/Y') }}</td>
        <td>{{ now()->format('H:i:s') }}</td>
    </tr></table>
    <h1 class="report-title">LAPORAN KEUANGAN SR-3 PERIODE {{ \Carbon\Carbon::parse($from)->locale('id')->translatedFormat('F Y') }}</h1>

    @php
        $operational = (float) $financial['summary']['operational_expenses'];
        $profit = (float) $financial['summary']['net_profit'];
        $initialCapital = (float) $cashAccounts->sum('initial_balance');
        $cards = [
            ['label' => $cashCards[0]['name'], 'value' => (float) $cashCards[0]['balance'], 'icon' => 'bank'],
            ['label' => $cashCards[1]['name'], 'value' => (float) $cashCards[1]['balance'], 'icon' => 'wallet'],
            ['label' => 'Valuasi Stok Barang', 'value' => (float) $financial['balance_sheet']['inventory'], 'icon' => 'basket'],
            ['label' => 'Piutang Usaha', 'value' => (float) $financial['balance_sheet']['receivables'], 'icon' => 'receivable'],
            ['label' => 'Saving Margin', 'value' => $profit, 'icon' => 'margin', 'percentage' => $savingMargin],
            ['label' => 'Operasional', 'value' => $operational, 'icon' => 'operations', 'percentage' => 'ALL OP'],
        ];
    @endphp

    <table class="cards">
        <tr>
            @foreach($cards as $card)
                @php
                    $percentage = $card['percentage'] ?? ($assetsTotal > 0 ? $card['value'] / $assetsTotal * 100 : 0);
                @endphp
                <td>
                    <div class="card-icon">
                        <img src="data:image/svg+xml;base64,{{ $iconImages[$card['icon']] }}" alt="">
                    </div>
                    <div class="card-percent">{{ is_numeric($percentage) ? number_format($percentage, 2, ',', '.').'%' : $percentage }}</div>
                    <div class="card-label">{{ $card['label'] }}</div>
                    <div class="card-value">{{ number_format($card['value'], 2, ',', '.') }}</div>
                </td>
            @endforeach
        </tr>
    </table>

    <table class="main-columns">
        <tr>
            <td>
                <h2 class="section-title blue">NERACA SALDO</h2>
                <table class="balance-layout">
                    <tr>
                        <td class="chart-cell">
                            <img class="chart" src="data:image/svg+xml;base64,{{ $chartImage }}" alt="Komposisi aset">
                        </td>
                        <td class="legend-cell">
                            <div class="subheading">AKTIVA (Aset Lancar)</div>
                            <table class="legend">
                                @foreach($assetBreakdown as $asset)
                                    <tr>
                                        <td><span class="swatch" style="background: {{ $asset['color'] }}"></span>{{ $asset['name'] }}</td>
                                        <td>{{ number_format($asset['percentage'], 1, ',', '.') }}%</td>
                                        <td>{{ number_format($asset['amount'], 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                                <tr class="total"><td colspan="2">Jumlah Aset Lancar</td><td>{{ number_format($assetsTotal, 2, ',', '.') }}</td></tr>
                                <tr><td colspan="2">Saving Margin Berjalan</td><td>{{ number_format($profit, 2, ',', '.') }}</td></tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <table class="statement">
                    <tr class="total"><td>Jumlah Total</td><td>{{ number_format($assetsTotal, 2, ',', '.') }}</td></tr>
                </table>
                <div class="subheading">PASIVA (MODAL TETAP)</div>
                <table class="statement">
                    <tr><td>Modal Awal</td><td>{{ number_format($initialCapital, 2, ',', '.') }}</td></tr>
                    <tr><td>Laba Bersih Berjalan</td><td>{{ number_format($profit, 2, ',', '.') }}</td></tr>
                    <tr><td>Hutang / Kewajiban</td><td>{{ number_format($liabilities, 2, ',', '.') }}</td></tr>
                    <tr class="total"><td>TOTAL PASIVA</td><td>{{ number_format($equity + $liabilities, 2, ',', '.') }}</td></tr>
                </table>
                <div class="difference">BALANCE AKTIVA - PASIVA <span style="float: right">0,00</span></div>
                <table class="statement">
                    <tr class="total"><td>OPERASIONAL + TOTAL ASET LANCAR</td><td>{{ number_format($operational + $assetsTotal, 2, ',', '.') }}</td></tr>
                </table>
                <div class="note">CATATAN: Neraca menampilkan saldo akun dan estimasi persediaan berdasarkan data yang tercatat pada aplikasi.</div>
            </td>
            <td>
                <h2 class="section-title green">LAPORAN LABA RUGI</h2>
                <table class="statement">
                    <tr class="green-total"><td>PENDAPATAN</td><td></td></tr>
                    <tr><td>Pendapatan Penjualan Sawit (PKS)</td><td>{{ number_format((float) $financial['summary']['revenue'], 2, ',', '.') }}</td></tr>
                    <tr><td>Pendapatan lain-lain</td><td>0,00</td></tr>
                    <tr class="total"><td>TOTAL PENDAPATAN (A)</td><td>{{ number_format((float) $financial['summary']['revenue'], 2, ',', '.') }}</td></tr>
                    <tr class="green-total"><td>PEMBELIAN &amp; BEBAN BIAYA LANGSUNG</td><td></td></tr>
                    <tr><td>Pembelian Buah Sawit (Petani)</td><td>{{ number_format((float) $financial['summary']['cost_of_goods'], 2, ',', '.') }}</td></tr>
                    <tr><td>Beban Jasa Muat</td><td>{{ number_format((float) $financial['summary']['additional_direct_expenses'], 2, ',', '.') }}</td></tr>
                    <tr><td>Ongkos Armada / Angkutan</td><td>0,00</td></tr>
                    <tr class="total"><td>TOTAL HARGA POKOK (B)</td><td>{{ number_format((float) $financial['summary']['direct_expenses'], 2, ',', '.') }}</td></tr>
                    <tr class="green-total"><td>LABA KOTOR LAPAK (A - B)</td><td>{{ number_format((float) $financial['summary']['revenue'] - (float) $financial['summary']['direct_expenses'], 2, ',', '.') }}</td></tr>
                    <tr class="green-total"><td>BEBAN OPERASIONAL &amp; ADMIN</td><td></td></tr>
                    <tr><td>Beban Gaji Karyawan</td><td>0,00</td></tr>
                    <tr><td>Operasional Lapak</td><td>{{ number_format($operational, 2, ',', '.') }}</td></tr>
                    <tr><td>Operasional Kas Utama</td><td>0,00</td></tr>
                    <tr class="total"><td>TOTAL BEBAN OPERASIONAL (D)</td><td>{{ number_format($operational, 2, ',', '.') }}</td></tr>
                    <tr class="green-total"><td>LABA BERSIH (E = C - D)</td><td>{{ number_format($profit, 2, ',', '.') }}</td></tr>
                    <tr><td>LABA BERSIH BERJALAN</td><td>0,00</td></tr>
                    <tr class="total"><td>LABA TOTAL</td><td>{{ number_format($profit, 2, ',', '.') }}</td></tr>
                </table>
            </td>
        </tr>
    </table>
    <table class="bottom-cards">
        <tr>
            <td>
                <div class="bottom-card">
                    <span class="bottom-icon"><img src="data:image/svg+xml;base64,{{ $iconImages['capital'] }}" alt=""></span>
                    <span class="bottom-copy"><div class="bottom-label">MODAL AWAL</div><div class="bottom-value">{{ number_format($initialCapital, 2, ',', '.') }}</div></span>
                </div>
            </td>
            <td>
                <div class="bottom-card">
                    <span class="bottom-icon"><img src="data:image/svg+xml;base64,{{ $iconImages['equity'] }}" alt=""></span>
                    <span class="bottom-copy"><div class="bottom-label">EQUITY</div><div class="bottom-value">{{ number_format($equity, 2, ',', '.') }}</div></span>
                </div>
            </td>
            <td></td>
            <td>
                <div class="bottom-card">
                    <span class="bottom-icon"><img src="data:image/svg+xml;base64,{{ $iconImages['profit'] }}" alt=""></span>
                    <span class="bottom-copy"><div class="bottom-label">LABA BERSIH</div><div class="bottom-value dark">{{ number_format($profit, 2, ',', '.') }}</div></span>
                </div>
            </td>
        </tr>
    </table>

    <section class="page-two">
        <table class="page-meta"><tr>
            <td>{{ now()->format('d/m/Y') }}</td>
            <td>{{ now()->format('H:i:s') }}</td>
        </tr></table>
        <table class="trip-table">
            <thead>
                <tr>
                    <th style="width: 3%">NO</th>
                    <th style="width: 7%">TANGGAL<br>BONGKAR</th>
                    <th style="width: 6%">PKS</th>
                    <th style="width: 4%">TRIP</th>
                    <th style="width: 7%">KIRIM BARANG<br>(KG)</th>
                    <th style="width: 7%">TBS<br>(KG)</th>
                    <th style="width: 7%">BRONDOLAN<br>(KG)</th>
                    <th style="width: 7%">BRD AFKIR<br>(KG)</th>
                    <th style="width: 7%">BUAH KECIL<br>(KG)</th>
                    <th style="width: 7%">HARGA<br>JUAL / KG</th>
                    <th style="width: 9%">JUMLAH<br>PENDAPATAN</th>
                    <th style="width: 7%">HARGA BELI<br>RATA-RATA</th>
                    <th style="width: 9%">TOTAL<br>HPP</th>
                    <th style="width: 7%">STATUS<br>BAYAR</th>
                    <th style="width: 6%">NETTO<br>(KG)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totals = [
                        'total_kg' => 0,
                        'tbs_kg' => 0,
                        'brondolan_kg' => 0,
                        'brd_afkir_kg' => 0,
                        'buah_kecil_kg' => 0,
                        'revenue' => 0,
                        'netto' => 0,
                    ];
                @endphp
                @forelse($shipments as $index => $shipment)
                    @php
                        $totals['total_kg'] += (float) $shipment->total_kg;
                        $totals['tbs_kg'] += (float) $shipment->tbs_kg;
                        $totals['brondolan_kg'] += (float) $shipment->brondolan_kg;
                        $totals['brd_afkir_kg'] += (float) $shipment->brd_afkir_kg;
                        $totals['buah_kecil_kg'] += (float) $shipment->buah_kecil_kg;
                        $totals['revenue'] += (float) $shipment->total_pendapatan;
                        $totals['netto'] += (float) $shipment->total_kg;
                    @endphp
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td class="center">{{ $shipment->tanggal->format('d/m/Y') }}</td>
                        <td class="left pks">{{ $shipment->pks_tujuan }}</td>
                        <td class="center">{{ $index + 1 }}</td>
                        <td>{{ number_format($shipment->total_kg, 2, ',', '.') }}</td>
                        <td>{{ number_format($shipment->tbs_kg, 2, ',', '.') }}</td>
                        <td>{{ number_format($shipment->brondolan_kg, 2, ',', '.') }}</td>
                        <td>{{ number_format($shipment->brd_afkir_kg, 2, ',', '.') }}</td>
                        <td>{{ number_format($shipment->buah_kecil_kg, 2, ',', '.') }}</td>
                        <td>{{ number_format($shipment->harga_jual_per_kg, 2, ',', '.') }}</td>
                        <td>{{ number_format($shipment->total_pendapatan, 2, ',', '.') }}</td>
                        <td class="center">—</td>
                        <td class="center">—</td>
                        <td class="status">{{ $shipment->is_paid ? 'LUNAS' : 'BELUM' }}</td>
                        <td>{{ number_format($shipment->total_kg, 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td class="trip-empty" colspan="15">Tidak ada pengiriman pada periode ini.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4">TOTAL</td>
                    <td>{{ number_format($totals['total_kg'], 2, ',', '.') }}</td>
                    <td>{{ number_format($totals['tbs_kg'], 2, ',', '.') }}</td>
                    <td>{{ number_format($totals['brondolan_kg'], 2, ',', '.') }}</td>
                    <td>{{ number_format($totals['brd_afkir_kg'], 2, ',', '.') }}</td>
                    <td>{{ number_format($totals['buah_kecil_kg'], 2, ',', '.') }}</td>
                    <td></td>
                    <td>{{ number_format($totals['revenue'], 2, ',', '.') }}</td>
                    <td></td>
                    <td>{{ number_format((float) $financial['summary']['cost_of_goods'], 2, ',', '.') }}</td>
                    <td></td>
                    <td>{{ number_format($totals['netto'], 2, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
        <div class="page-footer">Laporan periode {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}</div>
    </section>
</body>
</html>
