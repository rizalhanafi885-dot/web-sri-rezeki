<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Surat Jalan {{ $shipment->no_nota }}</title>
    <style>
        body { color: #222; font: 14px Arial, sans-serif; margin: 32px auto; max-width: 780px; }
        h1, h2, p { margin: 0; }
        h1 { font-size: 22px; text-align: center; }
        h2 { font-size: 16px; margin-top: 8px; text-align: center; }
        .header { border-bottom: 2px solid #222; padding-bottom: 16px; }
        .details { display: grid; grid-template-columns: 1fr 1fr; gap: 12px 24px; margin: 24px 0; }
        .label { color: #555; display: inline-block; min-width: 130px; }
        table { border-collapse: collapse; margin-top: 18px; width: 100%; }
        th, td { border: 1px solid #777; padding: 10px; text-align: left; }
        th:last-child, td:last-child { text-align: right; }
        tfoot { font-weight: bold; }
        .signatures { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-top: 64px; text-align: center; }
        .signature-space { height: 72px; }
        @media print { body { margin: 0; max-width: none; } }
    </style>
</head>
<body>
    <header class="header">
        <h1>SURAT JALAN PENGIRIMAN</h1>
        <h2>SUMBER REZEKI</h2>
    </header>

    <section class="details">
        <div><span class="label">No. Nota</span>: <strong>{{ $shipment->no_nota }}</strong></div>
        <div><span class="label">Tanggal</span>: {{ $shipment->tanggal->format('d/m/Y') }}</div>
        <div><span class="label">PKS Tujuan</span>: {{ $shipment->pks_tujuan }}</div>
        <div><span class="label">Nomor Polisi</span>: {{ $shipment->nopol }}</div>
        <div><span class="label">Nama Driver</span>: {{ $shipment->driver }}</div>
    </section>

    <table>
        <thead>
            <tr><th>Jenis Muatan</th><th>Berat (Kg)</th></tr>
        </thead>
        <tbody>
            @foreach(\App\Models\Shipment::CARGO_FIELDS as $category => $field)
                <tr>
                    <td>{{ $category }}</td>
                    <td>{{ number_format($shipment->{$field}, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Total Muatan</td>
                <td>{{ number_format($shipment->total_kg, 2, ',', '.') }} Kg</td>
            </tr>
        </tfoot>
    </table>

    @if($shipment->catatan)
        <p style="margin-top: 20px"><strong>Catatan:</strong> {{ $shipment->catatan }}</p>
    @endif

    <div class="signatures">
        <div><p>Pengirim</p><div class="signature-space"></div><p>(________________)</p></div>
        <div><p>Driver</p><div class="signature-space"></div><p>({{ $shipment->driver }})</p></div>
        <div><p>Penerima</p><div class="signature-space"></div><p>(________________)</p></div>
    </div>

    <script>window.addEventListener('load', () => window.print());</script>
</body>
</html>
