@extends('layouts.admin')

@section('content')
<style>
    .main-wrapper { min-width: 0; }
    .container-fluid { width: 100%; min-width: 0; }
    .d-flex { display: flex; }
    .justify-content-between { justify-content: space-between; }
    .align-items-center { align-items: center; }
    .mb-4 { margin-bottom: 20px; }
    .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; }
    .shadow-sm { box-shadow: 0 1px 3px rgba(0, 0, 0, .05); }
    .card-body { padding: 16px; }
    .table-responsive { max-width: 100%; overflow-x: auto; }
    .table { width: 100%; border-collapse: collapse; font-size: 13px; text-align: left; }
    .table th, .table td { padding: 12px 14px; border-bottom: 1px solid #e2e8f0; }
    .table th { color: #334155; font-weight: 700; white-space: nowrap; }
    .table-hover tbody tr:hover { background: #f8fafc; }
    .align-middle th, .align-middle td { vertical-align: middle; }
    .table-light { background: #f8fafc; }
    .badge { display: inline-block; border-radius: 4px; padding: 4px 7px; font-size: 11px; }
    .bg-warning { background: #fbbf24; }
    .text-dark { color: #1e293b; }
    .fw-bold { font-weight: 700; }
    .text-muted { color: #64748b; }
    .text-nowrap { white-space: nowrap; }
    .btn { display: inline-flex; align-items: center; justify-content: center; border: 0; border-radius: 5px; padding: 8px 12px; font: inherit; font-size: 13px; text-decoration: none; cursor: pointer; }
    .btn-sm { padding: 6px 9px; font-size: 12px; }
    .btn-success { color: #fff; background: #15803d; }
    .btn-warning { color: #1e293b; background: #fbbf24; }
    .btn-secondary { color: #fff; background: #64748b; }
    .btn-danger { color: #fff; background: #dc2626; }
    .btn:hover { filter: brightness(.95); }
    .modal { position: fixed; inset: 0; z-index: 200; display: none; align-items: flex-start; justify-content: center; overflow-y: auto; padding: 6vh 16px 24px; background: rgba(15, 23, 42, .55); }
    .modal.show { display: flex; }
    .modal-dialog { width: min(640px, 100%); margin: auto; }
    .modal-content { overflow: hidden; border-radius: 8px; background: #fff; box-shadow: 0 20px 50px rgba(15, 23, 42, .25); }
    .modal-header, .modal-footer { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; }
    .modal-header { background: #fbbf24; }
    .modal-title { font-size: 17px; font-weight: 600; }
    .btn-close { width: 30px; height: 30px; border: 0; background: transparent; color: #334155; font-size: 24px; cursor: pointer; }
    .btn-close::before { content: "×"; }
    .modal-body { padding: 18px 16px; }
    .modal-footer { justify-content: flex-end; gap: 8px; border-top: 1px solid #e2e8f0; }
    .row { display: grid; grid-template-columns: repeat(12, 1fr); gap: 14px; }
    .g-3 { gap: 14px; }
    .col-md-6 { grid-column: span 6; }
    .col-6 { grid-column: span 6; }
    .form-label { display: block; margin-bottom: 6px; font-size: 13px; color: #334155; }
    .form-control { width: 100%; min-height: 38px; border: 1px solid #cbd5e1; border-radius: 5px; padding: 8px 10px; font: inherit; font-size: 13px; }
    .form-control:focus { outline: 2px solid rgba(22, 163, 74, .18); border-color: #16a34a; }
    hr { margin: 18px 0; border: 0; border-top: 1px solid #e2e8f0; }
    .fw-bold.mb-3 { margin-bottom: 12px; }
    .text-end { text-align: right; }
    .mt-4 { margin-top: 20px; }
    .fs-5 { font-size: 18px; }
    .mt-3 { margin-top: 16px; }
    .alert { margin-bottom: 16px; border-radius: 6px; padding: 12px 14px; }
    .alert-success { background: #dcfce7; color: #166534; }
    .alert-danger { background: #fee2e2; color: #991b1b; }
    body.shipment-modal-open { overflow: hidden; }
    @media (min-width: 768px) { .col-md-3 { grid-column: span 3; } }
    @media (max-width: 767px) {
        .container-fluid h3 { font-size: 18px; }
        .card-body { padding: 10px; }
        .table th, .table td { padding: 9px 10px; }
        .col-md-6 { grid-column: span 12; }
        .modal { padding: 12px; }
    }
</style>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Pengiriman ke PKS</h3>
        <button class="btn btn-success" onclick="resetForm(); showShipmentModal()">
            + Catat Pengiriman
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No. Nota</th>
                        <th>Tanggal</th>
                        <th>PKS Tujuan</th>
                        <th>Nopol / Driver</th>
                        <th>Total (Kg)</th>
                        <th>Pendapatan (Rp)</th>
                        <th>Status Pembayaran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shipments as $shipment)
                        <tr>
                            <td>
                                <span class="badge bg-warning text-dark">{{ $shipment->no_nota }}</span>
                            </td>
                            <td>{{ $shipment->tanggal->format('d/m/Y') }}</td>
                            <td class="fw-bold">{{ $shipment->pks_tujuan }}</td>
                            <td>
                                {{ $shipment->nopol }}<br>
                                <small class="text-muted">{{ $shipment->driver }}</small>
                            </td>
                            <td class="fw-bold">{{ number_format($shipment->total_kg, 0, ',', '.') }} Kg</td>
                            <td class="fw-bold">Rp {{ number_format($shipment->total_pendapatan, 0, ',', '.') }}</td>
                            <td>{{ $shipment->is_paid ? 'Lunas · '.($shipment->cashAccount?->name ?? 'Akun') : 'Belum dibayar' }}</td>
                            <td class="text-nowrap">
                                <button class="btn btn-warning btn-sm"
                                    onclick="editShipment({{ Js::from([
                                        'id' => $shipment->id,
                                        'tanggal' => $shipment->tanggal->format('Y-m-d'),
                                        'pks_tujuan' => $shipment->pks_tujuan,
                                        'nopol' => $shipment->nopol,
                                        'driver' => $shipment->driver,
                                        'harga_jual_per_kg' => (int) $shipment->harga_jual_per_kg,
                                        'catatan' => $shipment->catatan,
                                        'cargo' => collect(\App\Models\Shipment::CARGO_FIELDS)
                                            ->mapWithKeys(fn ($field) => [$field => $shipment->{$field}]),
                                    ]) }})">
                                    Edit
                                </button>
                                <a href="{{ route('admin.shipments.print', $shipment) }}"
                                    target="_blank" class="btn btn-secondary btn-sm">Cetak SJ</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">Belum ada data pengiriman.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="shipmentModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="shipmentForm" action="{{ route('admin.shipments.store') }}" method="POST">
                @csrf
                <div id="methodField">
                    @if(old('_method') === 'PUT')
                        <input type="hidden" name="_method" value="PUT">
                        <input type="hidden" name="_shipment_id" value="{{ old('_shipment_id') }}">
                    @endif
                </div>

                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="modalTitle">Catat Pengiriman ke PKS</h5>
                    <button type="button" class="btn-close"
                        onclick="hideShipmentModal()" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="tanggal">Tanggal Pengiriman</label>
                            <input id="tanggal" type="date" name="tanggal" class="form-control"
                                value="{{ old('tanggal', now()->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="pks_tujuan">PKS Tujuan</label>
                            <input id="pks_tujuan" type="text" name="pks_tujuan" class="form-control"
                                value="{{ old('pks_tujuan') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="nopol">Nopol Kendaraan</label>
                            <input id="nopol" type="text" name="nopol" class="form-control"
                                value="{{ old('nopol') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="driver">Nama Driver</label>
                            <input id="driver" type="text" name="driver" class="form-control"
                                value="{{ old('driver') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="harga_jual_per_kg">Harga Jual per Kg (Rp)</label>
                            <input id="harga_jual_per_kg" type="text" inputmode="numeric" name="harga_jual_per_kg"
                                class="form-control" data-format-number data-number-integer data-number-min="1"
                                data-number-max="999999" value="{{ old('harga_jual_per_kg') }}"
                                placeholder="Contoh: 2.950" required>
                        </div>
                    </div>

                    <hr>
                    <h6 class="fw-bold mb-3">Rincian Muatan (Kg)</h6>
                    <div class="row g-3">
                        @foreach(\App\Models\Shipment::CARGO_FIELDS as $category => $field)
                            <div class="col-6 col-md-3">
                                <label class="form-label" for="{{ $field }}">{{ $category }}</label>
                                <input id="{{ $field }}" type="text" inputmode="decimal" name="{{ $field }}"
                                    class="form-control cargo-input" data-format-number data-number-min="0"
                                    data-number-decimals="2"
                                    data-number-max="9999999999.99" value="{{ old($field, '0') }}" required>
                                <small class="text-muted">
                                    Stok: <span data-stock-label="{{ $category }}">
                                        {{ number_format($stocks[$category], 2, ',', '.') }}
                                    </span> Kg
                                </small>
                            </div>
                        @endforeach
                    </div>

                    <div class="text-end mt-4 fs-5 fw-bold">
                        Total Muatan: <span class="text-danger"><span id="shipmentTotal">0</span> Kg</span>
                    </div>
                    <div class="text-end fw-bold">
                        Estimasi Pendapatan: <span class="text-success">Rp <span id="shipmentRevenue">0</span></span>
                    </div>

                    <div class="mt-3">
                        <label class="form-label" for="catatan">Catatan (Opsional)</label>
                        <textarea id="catatan" name="catatan" class="form-control" rows="2">{{ old('catatan') }}</textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        onclick="hideShipmentModal()">Batal</button>
                    <button type="submit" class="btn btn-warning">Simpan Pengiriman</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const shipmentCargoFields = @json(array_values(\App\Models\Shipment::CARGO_FIELDS));
const shipmentStocks = @json($stocks);
const cargoCategories = @json(\App\Models\Shipment::CARGO_FIELDS);

function formatKg(value) {
    return Number(value || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 });
}

function updateShipmentTotal() {
    const total = shipmentCargoFields.reduce((sum, field) => {
        return sum + window.parseFormattedNumber(document.querySelector('[name="' + field + '"]').value);
    }, 0);
    document.getElementById('shipmentTotal').textContent = formatKg(total);
    const price = window.parseFormattedNumber(document.getElementById('harga_jual_per_kg').value);
    document.getElementById('shipmentRevenue').textContent = Math.round(total * price)
        .toLocaleString('id-ID');
}

function showShipmentModal() {
    const modal = document.getElementById('shipmentModal');
    modal.classList.add('show');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('shipment-modal-open');
}

function hideShipmentModal() {
    const modal = document.getElementById('shipmentModal');
    modal.classList.remove('show');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('shipment-modal-open');
}

function showAvailableStocks(additionalCargo = {}) {
    for (const [category, field] of Object.entries(cargoCategories)) {
        const stock = Number(shipmentStocks[category] || 0) + Number(additionalCargo[field] || 0);
        document.querySelector('[data-stock-label="' + category + '"]').textContent = formatKg(stock);
    }
}

function resetForm() {
    const form = document.getElementById('shipmentForm');
    form.reset();
    form.action = @json(route('admin.shipments.store'));
    form.querySelector('[name="tanggal"]').value = new Date().toLocaleDateString('en-CA');
    document.getElementById('methodField').innerHTML = '';
    document.getElementById('modalTitle').textContent = 'Catat Pengiriman ke PKS';
    form.querySelector('[name="harga_jual_per_kg"]').value = '';
    for (const field of shipmentCargoFields) {
        form.querySelector('[name="' + field + '"]').value = '0';
        window.formatNumberInput(form.querySelector('[name="' + field + '"]'));
    }
    showAvailableStocks();
    updateShipmentTotal();
}

function editShipment(data) {
    const form = document.getElementById('shipmentForm');
    form.action = @json(url('/admin/shipments')) + '/' + data.id;
    document.getElementById('methodField').innerHTML =
        '<input type="hidden" name="_method" value="PUT">' +
        '<input type="hidden" name="_shipment_id" value="' + data.id + '">';

    for (const field of ['tanggal', 'pks_tujuan', 'nopol', 'driver', 'harga_jual_per_kg', 'catatan']) {
        form.querySelector('[name="' + field + '"]').value = data[field] || '';
    }
    window.formatNumberInput(form.querySelector('[name="harga_jual_per_kg"]'));
    for (const field of shipmentCargoFields) {
        form.querySelector('[name="' + field + '"]').value = data.cargo[field] || 0;
        window.formatNumberInput(form.querySelector('[name="' + field + '"]'));
    }

    showAvailableStocks(data.cargo);
    updateShipmentTotal();
    document.getElementById('modalTitle').textContent = 'Edit Pengiriman: ' + data.id;
    showShipmentModal();
}

document.querySelectorAll('.cargo-input').forEach(input => {
    input.addEventListener('input', updateShipmentTotal);
});
document.getElementById('harga_jual_per_kg').addEventListener('input', updateShipmentTotal);

document.getElementById('shipmentModal').addEventListener('click', event => {
    if (event.target.id === 'shipmentModal') {
        hideShipmentModal();
    }
});

document.addEventListener('keydown', event => {
    if (event.key === 'Escape') {
        hideShipmentModal();
    }
});

@if($errors->any())
const oldShipmentId = @json(old('_shipment_id'));
if (oldShipmentId) {
    document.getElementById('shipmentForm').action =
        @json(url('/admin/shipments')) + '/' + oldShipmentId;
    document.getElementById('modalTitle').textContent = 'Edit Pengiriman';
}
showShipmentModal();
@endif
updateShipmentTotal();
</script>
@endsection
