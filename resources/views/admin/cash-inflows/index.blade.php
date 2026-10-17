@extends('layouts.admin')

@section('title', 'Kas Masuk - Sumber Rezeki')

@section('content')
<style>
    .main-wrapper { min-width: 0; }
    .cash-inflow-heading { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 18px; }
    .cash-inflow-heading h1 { margin: 0; font-size: 20px; }
    .cash-inflow-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; border: 0; border-radius: 5px; padding: 8px 12px; font: inherit; font-size: 13px; cursor: pointer; }
    .cash-inflow-btn-primary { color: #fff; background: #198754; }
    .cash-inflow-btn-secondary { color: #fff; background: #6c757d; }
    .cash-inflow-btn-edit { color: #fff; background: #2563eb; }
    .cash-inflow-btn:hover { filter: brightness(.95); }
    .cash-inflow-alert { margin-bottom: 14px; border-radius: 6px; padding: 12px 14px; }
    .cash-inflow-alert-success { color: #166534; background: #dcfce7; }
    .cash-inflow-alert-danger { color: #991b1b; background: #fee2e2; }
    .cash-inflow-card { overflow: hidden; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; box-shadow: 0 1px 3px rgba(0, 0, 0, .05); }
    .cash-inflow-table-wrap { max-width: 100%; overflow-x: auto; padding: 12px; }
    .cash-inflow-table { width: 100%; border-collapse: collapse; font-size: 13px; text-align: left; }
    .cash-inflow-table th, .cash-inflow-table td { padding: 10px 9px; border-bottom: 1px solid #dbe2ea; white-space: nowrap; }
    .cash-inflow-table thead th { font-weight: 700; }
    .cash-inflow-no { display: inline-block; border-radius: 4px; padding: 4px 6px; color: #fff; background: #198754; font-size: 10px; font-weight: 700; }
    .cash-inflow-source { font-weight: 700; }
    .cash-inflow-amount { color: #198754; font-weight: 700; }
    .cash-inflow-empty { color: #64748b; text-align: center !important; }
    .cash-inflow-modal { position: fixed; inset: 0; z-index: 200; display: none; align-items: flex-start; justify-content: center; overflow-y: auto; padding: 8vh 16px 24px; background: rgba(15, 23, 42, .55); }
    .cash-inflow-modal.show { display: flex; }
    .cash-inflow-dialog { width: min(460px, 100%); margin: auto; }
    .cash-inflow-modal-content { overflow: hidden; border-radius: 8px; background: #fff; box-shadow: 0 20px 50px rgba(15, 23, 42, .25); }
    .cash-inflow-modal-header, .cash-inflow-modal-footer { display: flex; align-items: center; justify-content: space-between; padding: 13px 16px; }
    .cash-inflow-modal-header { color: #fff; background: #198754; }
    .cash-inflow-modal-header h2 { margin: 0; font-size: 16px; font-weight: 600; }
    .cash-inflow-close { border: 0; color: inherit; background: transparent; font-size: 24px; cursor: pointer; }
    .cash-inflow-modal-body { padding: 16px; }
    .cash-inflow-modal-footer { justify-content: flex-end; gap: 8px; border-top: 1px solid #e2e8f0; }
    .cash-inflow-form-group { margin-bottom: 14px; }
    .cash-inflow-form-group:last-child { margin-bottom: 0; }
    .cash-inflow-label { display: block; margin-bottom: 6px; color: #334155; font-size: 13px; }
    .cash-inflow-input { width: 100%; min-height: 38px; border: 1px solid #cbd5e1; border-radius: 5px; padding: 8px 10px; font: inherit; font-size: 13px; }
    .cash-inflow-input:focus { outline: 2px solid rgba(22, 163, 74, .18); border-color: #16a34a; }
    body.cash-inflow-modal-open { overflow: hidden; }
    @media (max-width: 650px) {
        .cash-inflow-heading { align-items: flex-start; }
        .cash-inflow-heading h1 { font-size: 18px; }
        .cash-inflow-modal { padding: 12px; }
    }
</style>

<div class="container-fluid">
    <div class="cash-inflow-heading">
        <h1>Pencatatan Kas Masuk</h1>
        <button type="button" class="cash-inflow-btn cash-inflow-btn-primary" onclick="resetCashInflowForm(); showCashInflowModal()">
            + Tambah Pemasukan
        </button>
    </div>

    @if(session('success'))
        <div class="cash-inflow-alert cash-inflow-alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="cash-inflow-alert cash-inflow-alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <section class="cash-inflow-card" aria-label="Daftar kas masuk">
        <div class="cash-inflow-table-wrap">
            <table class="cash-inflow-table">
                <thead>
                    <tr>
                        <th scope="col">No. Transaksi</th>
                        <th scope="col">Tanggal</th>
                        <th scope="col">Akun</th>
                        <th scope="col">Sumber</th>
                        <th scope="col">Keterangan</th>
                        <th scope="col">Nominal (Rp)</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inflows as $inflow)
                        <tr>
                            <td><span class="cash-inflow-no">{{ $inflow->no_transaksi }}</span></td>
                            <td>{{ $inflow->tanggal->format('d/m/Y') }}</td>
                            <td>{{ $inflow->cashAccount?->name ?? 'Kas Utama' }}</td>
                            <td class="cash-inflow-source">{{ $inflow->sumber }}</td>
                            <td>{{ $inflow->keterangan }}</td>
                            <td class="cash-inflow-amount">+ Rp {{ number_format($inflow->nominal, 0, ',', '.') }}</td>
                            <td>
                                <button type="button" class="cash-inflow-btn cash-inflow-btn-edit"
                                    onclick="editCashInflow({{ Js::from([
                                        'id' => $inflow->id,
                                        'tanggal' => $inflow->tanggal->format('Y-m-d'),
                                        'cash_account_id' => $inflow->cash_account_id,
                                        'sumber' => $inflow->sumber,
                                        'nominal' => (int) $inflow->nominal,
                                        'keterangan' => $inflow->keterangan,
                                    ]) }})">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="cash-inflow-empty">Belum ada data kas masuk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<div class="cash-inflow-modal" id="cashInflowModal" aria-hidden="true">
    <div class="cash-inflow-dialog">
        <div class="cash-inflow-modal-content">
            <form id="cashInflowForm" action="{{ route('admin.cash-inflows.store') }}" method="POST">
                @csrf
                <div id="cashInflowMethodField">
                    @if(old('_method') === 'PUT')
                        <input type="hidden" name="_method" value="PUT">
                        <input type="hidden" name="_cash_inflow_id" value="{{ old('_cash_inflow_id') }}">
                    @endif
                </div>
                <header class="cash-inflow-modal-header">
                    <h2 id="cashInflowModalTitle">Catat Kas Masuk</h2>
                    <button type="button" class="cash-inflow-close" aria-label="Tutup" onclick="hideCashInflowModal()">×</button>
                </header>
                <div class="cash-inflow-modal-body">
                    <div class="cash-inflow-form-group">
                        <label class="cash-inflow-label" for="cashInflowDate">Tanggal</label>
                        <input class="cash-inflow-input" id="cashInflowDate" type="date" name="tanggal"
                            value="{{ old('tanggal', now()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="cash-inflow-form-group">
                        <label class="cash-inflow-label" for="cashInflowAccount">Akun Kas/Bank</label>
                        <select class="cash-inflow-input" id="cashInflowAccount" name="cash_account_id" required>
                            @foreach($cashAccounts as $account)
                                <option value="{{ $account->id }}" @selected((string) old('cash_account_id', $cashAccounts->firstWhere('type', 'cash')?->id) === (string) $account->id)>
                                    {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="cash-inflow-form-group">
                        <label class="cash-inflow-label" for="cashInflowSource">Sumber Modal/Penerimaan</label>
                        <input class="cash-inflow-input" id="cashInflowSource" type="text" name="sumber"
                            maxlength="255" value="{{ old('sumber') }}" required>
                    </div>
                    <div class="cash-inflow-form-group">
                        <label class="cash-inflow-label" for="cashInflowAmount">Nominal (Rp)</label>
                        <input class="cash-inflow-input" id="cashInflowAmount" type="text" inputmode="numeric"
                            name="nominal" data-format-number data-number-integer data-number-min="1"
                            data-number-max="999999999999"
                            value="{{ old('nominal') }}" placeholder="Contoh: 1.000.000" required>
                    </div>
                    <div class="cash-inflow-form-group">
                        <label class="cash-inflow-label" for="cashInflowNote">Keterangan</label>
                        <textarea class="cash-inflow-input" id="cashInflowNote" name="keterangan"
                            rows="2" maxlength="5000" required>{{ old('keterangan') }}</textarea>
                    </div>
                </div>
                <footer class="cash-inflow-modal-footer">
                    <button type="button" class="cash-inflow-btn cash-inflow-btn-secondary" onclick="hideCashInflowModal()">Batal</button>
                    <button id="cashInflowSubmit" type="submit" class="cash-inflow-btn cash-inflow-btn-primary">+ Simpan Pemasukan</button>
                </footer>
            </form>
        </div>
    </div>
</div>

<script>
function showCashInflowModal() {
    const modal = document.getElementById('cashInflowModal');
    modal.classList.add('show');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('cash-inflow-modal-open');
}

function hideCashInflowModal() {
    const modal = document.getElementById('cashInflowModal');
    modal.classList.remove('show');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('cash-inflow-modal-open');
}

function resetCashInflowForm() {
    const form = document.getElementById('cashInflowForm');
    form.reset();
    form.action = @json(route('admin.cash-inflows.store'));
    document.getElementById('cashInflowMethodField').innerHTML = '';
    document.getElementById('cashInflowModalTitle').textContent = 'Catat Kas Masuk';
    document.getElementById('cashInflowSubmit').textContent = '+ Simpan Pemasukan';
    document.getElementById('cashInflowDate').value = new Date().toLocaleDateString('en-CA');
}

function editCashInflow(data) {
    const form = document.getElementById('cashInflowForm');
    form.action = @json(url('/admin/cash-inflows')) + '/' + data.id;
    document.getElementById('cashInflowMethodField').innerHTML =
        '<input type="hidden" name="_method" value="PUT">' +
        '<input type="hidden" name="_cash_inflow_id" value="' + data.id + '">';
    document.getElementById('cashInflowDate').value = data.tanggal;
    document.getElementById('cashInflowAccount').value = data.cash_account_id;
    document.getElementById('cashInflowSource').value = data.sumber;
    document.getElementById('cashInflowAmount').value = data.nominal;
    window.formatNumberInput(document.getElementById('cashInflowAmount'));
    document.getElementById('cashInflowNote').value = data.keterangan;
    document.getElementById('cashInflowModalTitle').textContent = 'Edit Kas Masuk: ' + data.id;
    document.getElementById('cashInflowSubmit').textContent = 'Simpan Perubahan';
    showCashInflowModal();
}

document.getElementById('cashInflowModal').addEventListener('click', event => {
    if (event.target.id === 'cashInflowModal') hideCashInflowModal();
});
document.addEventListener('keydown', event => {
    if (event.key === 'Escape') hideCashInflowModal();
});

@if($errors->any())
@if(old('_cash_inflow_id'))
document.getElementById('cashInflowForm').action =
    @json(url('/admin/cash-inflows')) + '/' + @json(old('_cash_inflow_id'));
document.getElementById('cashInflowModalTitle').textContent = 'Edit Kas Masuk';
document.getElementById('cashInflowSubmit').textContent = 'Simpan Perubahan';
@endif
showCashInflowModal();
@endif
</script>
@endsection
