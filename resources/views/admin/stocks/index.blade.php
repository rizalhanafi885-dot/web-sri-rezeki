@extends('layouts.admin')

@section('title', 'Informasi Stok & Persediaan - Sumber Rezeki')

@section('content')
<style>
    .main-wrapper { min-width: 0; }
    .container-fluid { width: 100%; min-width: 0; }
    .stock-heading { margin-bottom: 16px; font-size: 20px; }
    .stock-card { overflow: hidden; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; box-shadow: 0 1px 3px rgba(0, 0, 0, .05); }
    .stock-table-wrap { overflow-x: auto; padding: 12px; }
    .stock-table { width: 100%; border-collapse: collapse; font-size: 13px; text-align: center; }
    .stock-table th, .stock-table td { padding: 10px 12px; border: 1px solid #dbe2ea; white-space: nowrap; }
    .stock-table thead th { background: #f8fafc; font-weight: 700; }
    .stock-table tbody th { font-weight: 700; }
    .stock-table .available-heading, .stock-table .available-cell { background: #198754; color: #fff; font-weight: 700; }
    .stock-table tfoot th { background: #212529; color: #fff; text-align: right; }
    .stock-table tfoot td { background: #212529; color: #ffc107; font-weight: 700; }
    .stock-note { margin-top: 12px; border: 1px solid #9eeaf9; border-radius: 6px; background: #cff4fc; color: #055160; padding: 12px 14px; font-size: 13px; line-height: 1.55; }
    .stock-note p { margin: 0; }
    @media (max-width: 700px) {
        .stock-heading { font-size: 18px; }
        .stock-table-wrap { padding: 8px; }
        .stock-table th, .stock-table td { padding: 9px 10px; }
    }
</style>

<div class="container-fluid">
    <h1 class="stock-heading">Informasi Stok &amp; Persediaan</h1>

    <section class="stock-card" aria-label="Ringkasan stok barang">
        <div class="stock-table-wrap">
            <table class="stock-table">
                <thead>
                    <tr>
                        <th scope="col">Kategori Barang</th>
                        <th scope="col">Netto 1 Masuk (Kg)</th>
                        <th scope="col">Netto 2 Masuk (Kg)</th>
                        <th scope="col">Total Keluar (Kg)</th>
                        <th scope="col">Penyesuaian (Kg)</th>
                        <th scope="col" class="available-heading">Stok Tersedia (Netto 1, Kg)</th>
                        <th scope="col">Estimasi Nilai (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stockRows as $row)
                        <tr>
                            <th scope="row">{{ $row['kategori'] }}</th>
                            <td>{{ number_format($row['total_masuk'], 0, ',', '.') }} Kg</td>
                            <td>{{ number_format($row['total_netto_2'], 2, ',', '.') }} Kg</td>
                            <td>{{ number_format($row['total_keluar'], 0, ',', '.') }} Kg</td>
                            <td>{{ number_format($row['penyesuaian'], 0, ',', '.') }} Kg</td>
                            <td class="available-cell">{{ number_format($row['stok_tersedia'], 0, ',', '.') }} Kg</td>
                            <td>Rp {{ number_format($row['estimasi_nilai'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="5">TOTAL STOK BARANG (NETTO 1 TERSEDIA):</th>
                        <td>{{ number_format($totalStokTersedia, 2, ',', '.') }} Kg</td>
                    </tr>
                    <tr>
                        <th colspan="5">TOTAL NILAI STOK (NETTO 2 × HARGA/KG):</th>
                        <td>Rp {{ number_format($totalEstimasiNilai, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </section>

    <aside class="stock-note">
        <p>
            <strong>ⓘ Catatan:</strong>
            Netto 1 = Bruto − Tara. Netto 2 = Netto 1 − rafaksi.
            Stok tersedia (Netto 1) dihitung dari seluruh pembelian dikurangi pengiriman dan
            ditambah penyesuaian stok. Nilai pembelian dihitung dari jumlah Netto 2 × harga per Kg;
            nilai stok tersisa dialokasikan proporsional terhadap Netto 1 yang masih tersedia.
        </p>
    </aside>
</div>
@endsection
