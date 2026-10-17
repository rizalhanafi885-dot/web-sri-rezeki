@extends('layouts.admin')

@section('title', 'Kas Keluar - Sumber Rezeki')

@section('content')
<style>
    .main-wrapper { min-width: 0; }
    .cash-outflow-heading { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 18px; }
    .cash-outflow-heading h1 { margin: 0; font-size: 20px; }
    .cash-outflow-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; border: 0; border-radius: 5px; padding: 8px 12px; font: inherit; font-size: 13px; cursor: pointer; }
    .cash-outflow-btn-primary { color: #fff; background: #dc3545; }
    .cash-outflow-btn-secondary { color: #fff; background: #6c757d; }
    .cash-outflow-btn-edit { color: #fff; background: #2563eb; }
    .cash-outflow-btn:hover { filter: brightness(.95); }
    .cash-outflow-alert { margin-bottom: 14px; border-radius: 6px; padding: 12px 14px; }
    .cash-outflow-alert-success { color: #166534; background: #dcfce7; }
    .cash-outflow-alert-danger { color: #991b1b; background: #fee2e2; }
    .cash-outflow-card { overflow: hidden; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; box-shadow: 0 1px 3px rgba(0, 0, 0, .05); }
    .cash-outflow-table-wrap { max-width: 100%; overflow-x: auto; padding: 12px; }
    .cash-outflow-table { width: 100%; border-collapse: collapse; font-size: 13px; text-align: left; }
    .cash-outflow-table th, .cash-outflow-table td { padding: 10px 9px; border-bottom: 1px solid #dbe2ea; white-space: nowrap; }
    .cash-outflow-table thead th { font-weight: 700; }
    .cash-outflow-table tbody tr:hover { background: #f8fafc; }
    .cash-outflow-no { display: inline-block; border-radius: 4px; padding: 4px 6px; color: #fff; background: #dc3545; font-size: 10px; font-weight: 700; }
    .cash-outflow-category { font-weight: 700; }
    .cash-outflow-amount { color: #dc3545; font-weight: 700; }
    .cash-outflow-empty { color: #64748b; text-align: center !important; }
    .cash-outflow-modal { position: fixed; inset: 0; z-index: 200; display: none; align-items: flex-start; justify-content: center; overflow-y: auto; padding: 8vh 16px 24px; background: rgba(15, 23, 42, .55); }
    .cash-outflow-modal.show { display: flex; }
    .cash-outflow-dialog { width: min(460px, 100%); margin: auto; }
    .cash-outflow-modal-content { overflow: hidden; border-radius: 8px; background: #fff; box-shadow: 0 20px 50px rgba(15, 23, 42, .25); }
    .cash-outflow-modal-header, .cash-outflow-modal-footer { display: flex; align-items: center; justify-content: space-between; padding: 13px 16px; }
    .cash-outflow-modal-header { color: #fff; background: #dc3545; }
    .cash-outflow-modal-header h2 { margin: 0; font-size: 16px; font-weight: 600; }
    .cash-outflow-close { border: 0; color: inherit; background: transparent; font-size: 24px; cursor: pointer; }
    .cash-outflow-modal-body { padding: 16px; }
    .cash-outflow-modal-footer { justify-content: flex-end; gap: 8px; border-top: 1px solid #e2e8f0; }
    .cash-outflow-form-group { margin-bottom: 14px; }
    .cash-outflow-form-group:last-child { margin-bottom: 0; }
    .cash-outflow-label { display: block; margin-bottom: 6px; color: #334155; font-size: 13px; }
    .cash-outflow-input { width: 100%; min-height: 38px; border: 1px solid #cbd5e1; border-radius: 5px; padding: 8px 10px; font: inherit; font-size: 13px; }
    .cash-outflow-input:focus { outline: 2px solid rgba(220, 53, 69, .15); border-color: #dc3545; }
    body.cash-outflow-modal-open { overflow: hidden; }
    @media (max-width: 650px) {
        .cash-outflow-heading { align-items: flex-start; }
        .cash-outflow-heading h1 { font-size: 18px; }
        .cash-outflow-modal { padding: 12px; }
    }
</style>

<div class="container-fluid">
    <div class="cash-outflow-heading">
        <h1>Pencatatan Kas Keluar</h1>
        <button type="button" class="cash-outflow-btn cash-outflow-btn-primary" onclick="resetCashOutflowForm(); showCashOutflowModal()">
            + Catat Pengeluaran
        </button>
    </div>

    @if(session('success'))
        <div class="cash-outflow-alert cash-outflow-alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="cash-outflow-alert cash-outflow-alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <section class="cash-outflow-card" aria-label="Daftar kas keluar">
        <div class="cash-outflow-table-wrap">
            <table class="cash-outflow-table">
                <thead>
                    <tr>
                        <th scope="col">No. Transaksi</th>
                        <th scope="col">Tanggal</th>
                        <th scope="col">Akun</th>
                        <th scope="col">Kategori</th>
                        <th scope="col">Keterangan</th>
                        <th scope="col">Nominal (Rp)</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($outflows as $outflow)
                        <tr>
                            <td><span class="cash-outflow-no">{{ $outflow->no_transaksi }}</span></td>
                            <td>{{ $outflow->tanggal->format('d M Y') }}</td>
                            <td>{{ $outflow->cashAccount?->name ?? 'Kas Utama' }}</td>
                            <td class="cash-outflow-category">{{ $outflow->kategori }}</td>
                            <td>{{ $outflow->keterangan }}</td>
                            <td class="cash-outflow-amount">- Rp {{ number_format($outflow->nominal, 0, ',', '.') }}</td>
                            <td>
                                <button type="button" class="cash-outflow-btn cash-outflow-btn-edit"
                                    onclick="editCashOutflow({{ Js::from([
                                        'id' => $outflow->id,
                                        'tanggal' => $outflow->tanggal->format('Y-m-d'),
                                        'cash_account_id' => $outflow->cash_account_id,
                                        'kategori' => $outflow->kategori,
                                        'nominal' => (int) $outflow->nominal,
                                        'keterangan' => $outflow->keterangan,
                                    ]) }})">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="cash-outflow-empty">Belum ada data kas keluar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<div class="cash-outflow-modal" id="cashOutflowModal" aria-hidden="true">
    <div class="cash-outflow-dialog">
        <div class="cash-outflow-modal-content">
            <form id="cashOutflowForm" action="{{ route('admin.cash-outflows.store') }}" method="POST">
                @csrf
                <div id="cashOutflowMethodField">
                    @if(old('_method') === 'PUT')
                        <input type="hidden" name="_method" value="PUT">
                        <input type="hidden" name="_cash_outflow_id" value="{{ old('_cash_outflow_id') }}">
                    @endif
                </div>
                <header class="cash-outflow-modal-header">
                    <h2 id="cashOutflowModalTitle">Catat Kas Keluar</h2>
                    <button type="button" class="cash-outflow-close" aria-label="Tutup" onclick="hideCashOutflowModal()">×</button>
                </header>
                <div class="cash-outflow-modal-body">
                    <div class="cash-outflow-form-group">
                        <label class="cash-outflow-label" for="cashOutflowDate">Tanggal</label>
                        <input class="cash-outflow-input" id="cashOutflowDate" type="date" name="tanggal"
                            value="{{ old('tanggal', now()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="cash-outflow-form-group">
                        <label class="cash-outflow-label" for="cashOutflowAccount">Akun Kas/Bank</label>
                        <select class="cash-outflow-input" id="cashOutflowAccount" name="cash_account_id" required>
                            @foreach($cashAccounts as $account)
                                <option value="{{ $account->id }}" @selected((string) old('cash_account_id', $cashAccounts->firstWhere('type', 'cash')?->id) === (string) $account->id)>
                                    {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="cash-outflow-form-group">
                        <label class="cash-outflow-label" for="cashOutflowCategory">Kategori Pengeluaran</label>
                        <select class="cash-outflow-input" id="cashOutflowCategory" name="kategori" required>
                            <option value="">Pilih kategori pengeluaran</option>
                            @foreach($expenseCategories as $category => $classification)
                                <option value="{{ $category }}" @selected(old('kategori') === $category)>
                                    {{ $category }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="cash-outflow-form-group">
                        <label class="cash-outflow-label" for="cashOutflowAmount">Nominal (Rp)</label>
                        <input class="cash-outflow-input" id="cashOutflowAmount" type="text" inputmode="numeric"
                            name="nominal" data-format-number data-number-integer data-number-min="1"
                            data-number-max="999999999999"
                            value="{{ old('nominal') }}" placeholder="Contoh: 1.000.000" required>
                    </div>
                    <div class="cash-outflow-form-group">
                        <label class="cash-outflow-label" for="cashOutflowNote">Keterangan</label>
                        <textarea class="cash-outflow-input" id="cashOutflowNote" name="keterangan"
                            rows="2" maxlength="5000" required>{{ old('keterangan') }}</textarea>
                    </div>
                </div>
                <footer class="cash-outflow-modal-footer">
                    <button type="button" class="cash-outflow-btn cash-outflow-btn-secondary" onclick="hideCashOutflowModal()">Batal</button>
                    <button id="cashOutflowSubmit" type="submit" class="cash-outflow-btn cash-outflow-btn-primary">− Catat Pengeluaran</button>
                </footer>
            </form>
        </div>
    </div>
</div>

<script>
function showCashOutflowModal() {
    const modal = document.getElementById('cashOutflowModal');
    modal.classList.add('show');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('cash-outflow-modal-open');
}

function hideCashOutflowModal() {
    const modal = document.getElementById('cashOutflowModal');
    modal.classList.remove('show');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('cash-outflow-modal-open');
}

function resetCashOutflowForm() {
    const form = document.getElementById('cashOutflowForm');
    form.reset();
    form.action = @json(route('admin.cash-outflows.store'));
    document.getElementById('cashOutflowMethodField').innerHTML = '';
    document.getElementById('cashOutflowModalTitle').textContent = 'Catat Kas Keluar';
    document.getElementById('cashOutflowSubmit').textContent = '− Catat Pengeluaran';
    document.getElementById('cashOutflowDate').value = new Date().toLocaleDateString('en-CA');
}

function editCashOutflow(data) {
    const form = document.getElementById('cashOutflowForm');
    const categorySelect = document.getElementById('cashOutflowCategory');
    form.action = @json(url('/admin/cash-outflows')) + '/' + data.id;
    document.getElementById('cashOutflowMethodField').innerHTML =
        '<input type="hidden" name="_method" value="PUT">' +
        '<input type="hidden" name="_cash_outflow_id" value="' + data.id + '">';
    document.getElementById('cashOutflowDate').value = data.tanggal;
    document.getElementById('cashOutflowAccount').value = data.cash_account_id;
    if (![...categorySelect.options].some(option => option.value === data.kategori)) {
        categorySelect.add(new Option(data.kategori, data.kategori));
    }
    categorySelect.value = data.kategori;
    document.getElementById('cashOutflowAmount').value = data.nominal;
    window.formatNumberInput(document.getElementById('cashOutflowAmount'));
    document.getElementById('cashOutflowNote').value = data.keterangan;
    document.getElementById('cashOutflowModalTitle').textContent = 'Edit Kas Keluar: ' + data.id;
    document.getElementById('cashOutflowSubmit').textContent = 'Simpan Perubahan';
    showCashOutflowModal();
}

document.getElementById('cashOutflowModal').addEventListener('click', event => {
    if (event.target.id === 'cashOutflowModal') hideCashOutflowModal();
});
document.addEventListener('keydown', event => {
    if (event.key === 'Escape') hideCashOutflowModal();
});

@if($errors->any())
@if(old('_cash_outflow_id'))
document.getElementById('cashOutflowForm').action =
    @json(url('/admin/cash-outflows')) + '/' + @json(old('_cash_outflow_id'));
document.getElementById('cashOutflowModalTitle').textContent = 'Edit Kas Keluar';
document.getElementById('cashOutflowSubmit').textContent = 'Simpan Perubahan';
@endif
showCashOutflowModal();
@endif
</script>
@endsection
