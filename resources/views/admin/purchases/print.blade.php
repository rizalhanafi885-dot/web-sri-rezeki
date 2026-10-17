<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Timbangan - {{ $purchase->no_nota }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Courier New', Courier, monospace;
        }

        body {
            background-color: #f1f5f9;
            padding: 20px;
            display: flex;
            justify-content: center;
        }

        .receipt-card {
            background: #ffffff;
            width: 380px;
            padding: 24px 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        .header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 12px;
            margin-bottom: 14px;
        }

        .header h1 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }

        .header p {
            font-size: 11px;
            color: #475569;
        }

        .info-table, .items-table {
            width: 100%;
            font-size: 12px;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .info-table td {
            padding: 3px 0;
        }

        .items-table th {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 6px 0;
            text-align: left;
        }

        .items-table td {
            padding: 6px 0;
        }

        .total-section {
            border-top: 2px dashed #000;
            border-bottom: 2px dashed #000;
            padding: 10px 0;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        .total-row.grand-total {
            font-size: 15px;
            font-weight: 800;
            margin-top: 6px;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            text-align: center;
            font-size: 11px;
            margin-top: 20px;
        }

        .sign-box {
            width: 45%;
        }

        .sign-space {
            height: 50px;
        }

        /* Tombol print untuk layar komputer */
        .actions-bar {
            text-align: center;
            margin-top: 20px;
        }

        .btn-print {
            background-color: #15803d;
            color: white;
            border: none;
            padding: 8px 20px;
            font-size: 13px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            font-family: Arial, sans-serif;
        }

        @media print {
            body {
                background: none;
                padding: 0;
            }
            .receipt-card {
                border: none;
                box-shadow: none;
                width: 100%;
            }
            .actions-bar {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div>
        <div class="receipt-card">
            <div class="header">
                <h1>SUMBER REZEKI</h1>
                <p>Penerimaan Tandan Buah Segar (TBS)</p>
            </div>

            <table class="info-table">
                <tr>
                    <td style="width: 35%;">No. Nota</td>
                    <td style="width: 5%;">:</td>
                    <td><strong>{{ $purchase->no_nota }}</strong></td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td>:</td>
                    <td>{{ $purchase->tanggal->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td>Nama Petani</td>
                    <td>:</td>
                    <td><strong>{{ $purchase->petani }}</strong></td>
                </tr>
                <tr>
                    <td>Kategori</td>
                    <td>:</td>
                    <td>{{ $purchase->kategori }}</td>
                </tr>
            </table>

            <table class="items-table">
                <thead>
                    <tr>
                        <th>Uraian</th>
                        <th style="text-align: right;">Hasil Timbangan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Bruto</td>
                        <td style="text-align: right;">{{ number_format($purchase->bruto, 0, ',', '.') }} Kg</td>
                    </tr>
                    <tr>
                        <td>Tara</td>
                        <td style="text-align: right;">{{ number_format($purchase->tara, 0, ',', '.') }} Kg</td>
                    </tr>
                    <tr>
                        <td><strong>Netto 1</strong></td>
                        <td style="text-align: right;"><strong>{{ number_format($purchase->netto_1, 1, ',', '.') }} Kg</strong></td>
                    </tr>
                    <tr>
                        <td>Potongan Rafaksi ({{ $purchase->rafaksi }}%)</td>
                        <td style="text-align: right;">- {{ number_format($purchase->netto_1 - $purchase->netto_2, 1, ',', '.') }} Kg</td>
                    </tr>
                    <tr>
                        <td><strong>Netto 2 (Dasar Bayar)</strong></td>
                        <td style="text-align: right;"><strong>{{ number_format($purchase->netto_2, 1, ',', '.') }} Kg</strong></td>
                    </tr>
                    <tr>
                        <td>Harga Beli / Kg</td>
                        <td style="text-align: right;">Rp {{ number_format($purchase->harga_per_kg, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="total-section">
                <div class="total-row">
                    <span>Total Pembelian:</span>
                    <span>Rp {{ number_format($purchase->total_pembelian, 0, ',', '.') }}</span>
                </div>
                @if($purchase->upah_bongkar > 0)
                <div class="total-row">
                    <span>Potongan Bongkar:</span>
                    <span>- Rp {{ number_format($purchase->upah_bongkar, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="total-row grand-total">
                    <span>TOTAL DIBAYAR:</span>
                    <span>Rp {{ number_format($purchase->total_bayar, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="signatures">
                <div class="sign-box">
                    <div>Petani / Pengirim,</div>
                    <div class="sign-space"></div>
                    <div>( {{ $purchase->petani }} )</div>
                </div>
                <div class="sign-box">
                    <div>Petugas Timbang,</div>
                    <div class="sign-space"></div>
                    <div>( Admin )</div>
                </div>
            </div>
        </div>

        <div class="actions-bar">
            <button class="btn-print" onclick="window.print()">
                Cetak / Print Struk Nota
            </button>
        </div>
    </div>

</body>
</html>
