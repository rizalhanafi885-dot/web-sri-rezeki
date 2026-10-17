@extends('layouts.admin')

@section('title', 'Manajemen Piutang - Sumber Rezeki')

@section('content')
<style>
    .main-wrapper { min-width: 0; }
    .container-fluid { width: 100%; min-width: 0; }
    .receivable-heading-row { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 18px; }
    .receivable-heading { margin: 0; font-size: 20px; }
    .receivable-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; border: 0; border-radius: 5px; padding: 8px 12px; font: inherit; font-size: 13px; text-decoration: none; cursor: pointer; }
    .receivable-btn:hover { filter: brightness(.95); }
    .receivable-btn-green { background: #198754; color: #fff; }
    .receivable-btn-yellow { background: #ffc107; color: #212529; }
    .receivable-btn-cyan { background: #0dcaf0; color: #fff; }
    .receivable-btn-secondary { background: #6c757d; color: #fff; }
    .receivable-btn-small { padding: 6px 10px; font-size: 12px; }
    .receivable-summary { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; margin-bottom: 18px; }
    .receivable-summary-card { min-height: 76px; border-radius: 7px; padding: 14px; color: #fff; box-shadow: 0 2px 4px rgba(0, 0, 0, .12); }
    .summary-blue { background: #0d6efd; }
    .summary-green { background: #198754; }
    .summary-red { background: #dc3545; }
    .summary-label { margin-bottom: 3px; font-size: 12px; }
    .summary-value { font-size: 18px; font-weight: 700; }
    .receivable-filter-card, .receivable-list-card { margin-bottom: 14px; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; box-shadow: 0 1px 3px rgba(0, 0, 0, .05); }
    .receivable-filter-card { padding: 12px 14px; }
    .receivable-filter { display: flex; flex-wrap: wrap; gap: 8px; }
    .receivable-control { min-height: 38px; border: 1px solid #cbd5e1; border-radius: 5px; background: #fff; padding: 8px 10px; font: inherit; font-size: 13px; }
    .receivable-search { width: min(360px, 100%); }
    .receivable-status-filter { min-width: 200px; }
    .receivable-list-wrap { max-width: 100%; overflow-x: auto; padding: 12px; }
    .receivable-table { width: 100%; border-collapse: collapse; font-size: 13px; text-align: left; }
    .receivable-table th, .receivable-table td { padding: 10px 9px; border-bottom: 1px solid #dbe2ea; white-space: nowrap; }
    .receivable-table thead th { background: #f8fafc; font-weight: 700; }
    .receivable-table tbody tr:hover { background: #f8fafc; }
    .receivable-nota { display: inline-block; border-radius: 4px; background: #64748b; color: #fff; padding: 4px 6px; font-size: 10px; font-weight: 700; }
    .receivable-customer { font-weight: 700; }
    .receivable-green-text { color: #198754; }
    .receivable-red-text { color: #dc3545; font-weight: 700; }
    .receivable-status { display: inline-block; border-radius: 4px; padding: 4px 7px; color: #fff; font-size: 10px; font-weight: 700; }
    .status-unpaid { background: #dc3545; }
    .status-partial { background: #ffc107; color: #212529; }
    .status-paid { background: #198754; }
    .receivable-actions { text-align: center; }
    .receivable-empty { color: #64748b; text-align: center !important; }
    .receivable-alert { margin-bottom: 14px; border-radius: 6px; padding: 12px 14px; }
    .receivable-alert-success { background: #dcfce7; color: #166534; }
    .receivable-alert-danger { background: #fee2e2; color: #991b1b; }
    .receivable-modal { position: fixed; inset: 0; z-index: 200; display: none; align-items: flex-start; justify-content: center; overflow-y: auto; padding: 8vh 16px 24px; background: rgba(15, 23, 42, .55); }
    .receivable-modal.show { display: flex; }
    #detailModal { z-index: 210; }
    #loanModal, #paymentModal { z-index: 220; }
    .receivable-modal-dialog { width: min(540px, 100%); margin: auto; }
    .receivable-modal-wide { width: min(720px, 100%); }
    .receivable-modal-content { overflow: hidden; border-radius: 8px; background: #fff; box-shadow: 0 20px 50px rgba(15, 23, 42, .25); }
    .receivable-modal-header, .receivable-modal-footer { display: flex; align-items: center; justify-content: space-between; padding: 13px 16px; }
    .receivable-modal-header { background: #198754; color: #fff; }
    .receivable-modal-header.yellow { background: #ffc107; color: #212529; }
    .receivable-modal-header.gray { background: #6c757d; color: #fff; }
    .receivable-modal-title { margin: 0; font-size: 16px; font-weight: 600; }
    .receivable-close { border: 0; background: transparent; color: inherit; font-size: 24px; cursor: pointer; }
    .receivable-modal-body { padding: 16px; }
    .receivable-modal-footer { justify-content: flex-end; gap: 8px; border-top: 1px solid #e2e8f0; }
    .receivable-form-group { margin-bottom: 14px; }
    .receivable-label { display: block; margin-bottom: 6px; color: #334155; font-size: 13px; }
    .receivable-input { width: 100%; min-height: 38px; border: 1px solid #cbd5e1; border-radius: 5px; background: #fff; padding: 8px 10px; font: inherit; font-size: 13px; }
    .receivable-input:focus { outline: 2px solid rgba(22, 163, 74, .18); border-color: #16a34a; }
    .receivable-readonly { background: #f1f5f9; color: #475569; }
    .receivable-detail-summary { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 20px; border-radius: 6px; background: #f8fafc; padding: 14px; font-size: 13px; }
    .receivable-detail-summary strong { color: #0f172a; }
    .receivable-detail-actions { display: flex; justify-content: flex-end; gap: 8px; margin: 14px 0; }
    .receivable-history-title { margin: 14px 0 8px; font-size: 14px; }
    .receivable-history-wrap { max-width: 100%; overflow-x: auto; }
    .receivable-history { width: 100%; border-collapse: collapse; font-size: 12px; }
    .receivable-history th, .receivable-history td { padding: 8px 9px; border: 1px solid #dbe2ea; text-align: left; white-space: nowrap; }
    .receivable-history th { background: #f8fafc; }
    .receivable-max-hint { margin-top: 5px; color: #64748b; font-size: 12px; }
    body.receivable-modal-open { overflow: hidden; }
    @media (max-width: 850px) {
        .receivable-table { min-width: 950px; }
    }
    @media (max-width: 650px) {
        .receivable-heading-row { align-items: flex-start; }
        .receivable-heading { font-size: 18px; }
        .receivable-summary { grid-template-columns: 1fr; gap: 8px; }
        .receivable-summary-card { min-height: 66px; }
        .receivable-modal { padding: 12px; }
        .receivable-detail-summary { grid-template-columns: 1fr; }
    }
</style>

<div class="container-fluid">
    <div class="receivable-heading-row">
        <h1 class="receivable-heading">Manajemen Piutang Pelanggan / Petani</h1>
        <button type="button" class="receivable-btn receivable-btn-green" onclick="resetDebtForm(); showReceivableModal('debtModal')">
            + Tambah Piutang
        </button>
    </div>

    @if(session('success'))
        <div class="receivable-alert receivable-alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="receivable-alert receivable-alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="receivable-summary">
        <section class="receivable-summary-card summary-blue">
            <div class="summary-label">Total Nominal Utang Awal</div>
            <div class="summary-value">Rp {{ number_format($initialTotal, 0, ',', '.') }}</div>
        </section>
        <section class="receivable-summary-card summary-green">
            <div class="summary-label">Total Cicilan Diterima</div>
            <div class="summary-value">Rp {{ number_format($paidTotal, 0, ',', '.') }}</div>
        </section>
        <section class="receivable-summary-card summary-red">
            <div class="summary-label">Total Sisa Utang</div>
            <div class="summary-value">Rp {{ number_format($balanceTotal, 0, ',', '.') }}</div>
        </section>
    </div>

    <section class="receivable-filter-card" aria-label="Filter piutang">
        <form method="GET" action="{{ route('admin.receivables.index') }}" class="receivable-filter">
            <input class="receivable-control receivable-search" type="search" name="q"
                value="{{ $filters['q'] ?? '' }}" placeholder="Cari nama pelanggan / No. Transaksi...">
            <select class="receivable-control receivable-status-filter" name="status">
                <option value="">Semua Status</option>
                @foreach(['Belum Lunas', 'Cicilan', 'Lunas'] as $status)
                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>
                @endforeach
            </select>
            <button class="receivable-btn receivable-btn-green" type="submit">Cari</button>
        </form>
    </section>

    <section class="receivable-list-card" aria-label="Daftar piutang">
        <div class="receivable-list-wrap">
            <table class="receivable-table">
                <thead>
                    <tr>
                        <th scope="col">No. Transaksi</th>
                        <th scope="col">Tanggal</th>
                        <th scope="col">Pelanggan</th>
                        <th scope="col">Pemberi Pinjaman</th>
                        <th scope="col">Utang Awal</th>
                        <th scope="col">Total Dibayar</th>
                        <th scope="col">Sisa Utang</th>
                        <th scope="col">Status</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receivables as $receivable)
                        <tr>
                            <td><span class="receivable-nota">{{ $receivable->no_transaksi }}</span></td>
                            <td>{{ $receivable->tanggal->format('d M Y') }}</td>
                            <td class="receivable-customer">{{ $receivable->pelanggan }}</td>
                            <td>{{ $receivable->pemberi_pinjaman }}</td>
                            <td>Rp {{ number_format($receivable->nominal_awal, 0, ',', '.') }}</td>
                            <td class="receivable-green-text">Rp {{ number_format($receivable->total_dibayar, 0, ',', '.') }}</td>
                            <td class="{{ $receivable->sisa_utang > 0 ? 'receivable-red-text' : 'receivable-green-text' }}">
                                Rp {{ number_format($receivable->sisa_utang, 0, ',', '.') }}
                            </td>
                            <td>
                                <span class="receivable-status {{ $receivable->status === 'Lunas' ? 'status-paid' : ($receivable->status === 'Cicilan' ? 'status-partial' : 'status-unpaid') }}">
                                    {{ $receivable->status }}
                                </span>
                            </td>
                            <td class="receivable-actions">
                                <button type="button" class="receivable-btn receivable-btn-cyan receivable-btn-small"
                                    onclick="openReceivableDetail({{ $receivable->id }})">Detail</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="receivable-empty">Belum ada data piutang.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<div class="receivable-modal" id="debtModal" aria-hidden="true">
    <div class="receivable-modal-dialog">
        <div class="receivable-modal-content">
            <form id="debtForm" action="{{ route('admin.receivables.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_form_context" value="debt">
                <header class="receivable-modal-header">
                    <h2 class="receivable-modal-title">Tambah Piutang Baru</h2>
                    <button type="button" class="receivable-close" aria-label="Tutup" onclick="hideReceivableModal('debtModal')">×</button>
                </header>
                <div class="receivable-modal-body">
                    <div class="receivable-form-group">
                        <label class="receivable-label" for="debtCustomer">Nama Pelanggan / Petani</label>
                        <input class="receivable-input" id="debtCustomer" name="pelanggan" maxlength="255"
                            value="{{ old('pelanggan') }}" placeholder="Masukkan nama pelanggan" required>
                    </div>
                    <div class="receivable-form-group">
                        <label class="receivable-label" for="debtDate">Tanggal Utang</label>
                        <input class="receivable-input" id="debtDate" type="date" name="tanggal"
                            value="{{ old('tanggal', now()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="receivable-form-group">
                        <label class="receivable-label" for="debtCashAccount">Akun Pencairan</label>
                        <select class="receivable-input" id="debtCashAccount" name="cash_account_id" required>
                            @foreach($cashAccounts as $account)
                                <option value="{{ $account->id }}" @selected((string) old('cash_account_id', $cashAccounts->firstWhere('type', 'cash')?->id) === (string) $account->id)>{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="receivable-form-group">
                        <label class="receivable-label" for="debtAmount">Nominal Utang (Rp)</label>
                        <input class="receivable-input" id="debtAmount" type="text" inputmode="decimal"
                            name="nominal_awal" data-format-number data-number-min="0.01" data-number-decimals="2"
                            data-number-max="999999999999.99" value="{{ old('nominal_awal') }}"
                            placeholder="Contoh: 500.000" required>
                    </div>
                    <div class="receivable-form-group">
                        <label class="receivable-label" for="debtLender">Admin Pemberi Pinjaman</label>
                        <input class="receivable-input receivable-readonly" id="debtLender" value="{{ $adminName }}" readonly>
                    </div>
                    <div class="receivable-form-group">
                        <label class="receivable-label" for="debtNote">Keterangan / Catatan</label>
                        <textarea class="receivable-input" id="debtNote" name="catatan" rows="2" maxlength="5000" placeholder="Opsional">{{ old('catatan') }}</textarea>
                    </div>
                </div>
                <footer class="receivable-modal-footer">
                    <button type="button" class="receivable-btn receivable-btn-secondary" onclick="hideReceivableModal('debtModal')">Batal</button>
                    <button type="submit" class="receivable-btn receivable-btn-green">Simpan Piutang</button>
                </footer>
            </form>
        </div>
    </div>
</div>

<div class="receivable-modal" id="detailModal" aria-hidden="true">
    <div class="receivable-modal-dialog receivable-modal-wide">
        <div class="receivable-modal-content">
            <header class="receivable-modal-header gray">
                <h2 class="receivable-modal-title">Detail Piutang &amp; Cicilan</h2>
                <button type="button" class="receivable-close" aria-label="Tutup" onclick="hideReceivableModal('detailModal')">×</button>
            </header>
            <div class="receivable-modal-body">
                <div class="receivable-detail-summary" id="receivableDetailSummary"></div>
                <div class="receivable-detail-actions">
                    <button type="button" class="receivable-btn receivable-btn-yellow" id="openLoanButton" onclick="openLoanForm()">Tambah Pinjaman</button>
                    <button type="button" class="receivable-btn receivable-btn-green" id="openPaymentButton" onclick="openPaymentForm()">+ Catat Cicilan</button>
                </div>
                <h3 class="receivable-history-title">Riwayat Pembayaran Cicilan</h3>
                <div class="receivable-history-wrap">
                    <table class="receivable-history">
                        <thead>
                            <tr>
                                <th>No. Pembayaran</th>
                                <th>Tanggal</th>
                                <th>Nominal</th>
                                <th>Metode</th>
                                <th>Admin Penerima</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody id="receivableHistory">
                            <tr><td colspan="6" class="receivable-empty">Memuat riwayat...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="receivable-modal" id="loanModal" aria-hidden="true">
    <div class="receivable-modal-dialog">
        <div class="receivable-modal-content">
            <form id="loanForm" method="POST">
                @csrf
                <input type="hidden" name="_form_context" value="loan">
                <input type="hidden" name="receivable_id" id="loanReceivableId">
                <header class="receivable-modal-header yellow">
                    <h2 class="receivable-modal-title">Tambah Pinjaman Pelanggan</h2>
                    <button type="button" class="receivable-close" aria-label="Tutup" onclick="hideReceivableModal('loanModal')">×</button>
                </header>
                <div class="receivable-modal-body">
                    <div class="receivable-form-group">
                        <label class="receivable-label" for="loanCustomer">Pelanggan</label>
                        <input class="receivable-input receivable-readonly" id="loanCustomer" readonly>
                    </div>
                    <div class="receivable-form-group">
                        <label class="receivable-label" for="loanDate">Tanggal Pinjaman Tambahan</label>
                        <input class="receivable-input" id="loanDate" type="date" name="tanggal"
                            value="{{ old('_form_context') === 'loan' ? old('tanggal', now()->format('Y-m-d')) : now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="receivable-form-group">
                        <label class="receivable-label" for="loanCashAccount">Akun Pencairan</label>
                        <select class="receivable-input" id="loanCashAccount" name="cash_account_id" required>
                            @foreach($cashAccounts as $account)
                                <option value="{{ $account->id }}" @selected((string) old('cash_account_id', $cashAccounts->firstWhere('type', 'cash')?->id) === (string) $account->id)>{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="receivable-form-group">
                        <label class="receivable-label" for="loanAmount">Nominal Tambahan Pinjaman (Rp)</label>
                        <input class="receivable-input" id="loanAmount" type="text" inputmode="decimal"
                            name="nominal" data-format-number data-number-min="0.01" data-number-decimals="2"
                            data-number-max="999999999999.99"
                            value="{{ old('_form_context') === 'loan' ? old('nominal') : '' }}"
                            placeholder="Contoh: 200000" required>
                    </div>
                    <div class="receivable-form-group">
                        <label class="receivable-label" for="loanNote">Keterangan / Keperluan</label>
                        <textarea class="receivable-input" id="loanNote" name="catatan" rows="2" maxlength="5000" placeholder="Opsional">{{ old('_form_context') === 'loan' ? old('catatan') : '' }}</textarea>
                    </div>
                </div>
                <footer class="receivable-modal-footer">
                    <button type="button" class="receivable-btn receivable-btn-secondary" onclick="hideReceivableModal('loanModal')">Batal</button>
                    <button type="submit" class="receivable-btn receivable-btn-yellow">Cairkan Pinjaman</button>
                </footer>
            </form>
        </div>
    </div>
</div>

<div class="receivable-modal" id="paymentModal" aria-hidden="true">
    <div class="receivable-modal-dialog">
        <div class="receivable-modal-content">
            <form id="paymentForm" method="POST">
                @csrf
                <input type="hidden" name="_form_context" value="payment">
                <input type="hidden" name="receivable_id" id="paymentReceivableId">
                <header class="receivable-modal-header">
                    <h2 class="receivable-modal-title">Form Bayar Cicilan</h2>
                    <button type="button" class="receivable-close" aria-label="Tutup" onclick="hideReceivableModal('paymentModal')">×</button>
                </header>
                <div class="receivable-modal-body">
                    <div class="receivable-form-group">
                        <label class="receivable-label" for="paymentDate">Tanggal Pembayaran</label>
                        <input class="receivable-input" id="paymentDate" type="date" name="tanggal"
                            value="{{ old('_form_context') === 'payment' ? old('tanggal', now()->format('Y-m-d')) : now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="receivable-form-group">
                        <label class="receivable-label" for="paymentCashAccount">Akun Penerima</label>
                        <select class="receivable-input" id="paymentCashAccount" name="cash_account_id" required>
                            @foreach($cashAccounts as $account)
                                <option value="{{ $account->id }}" @selected((string) old('cash_account_id', $cashAccounts->firstWhere('type', 'cash')?->id) === (string) $account->id)>{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="receivable-form-group">
                        <label class="receivable-label" for="paymentAmount">Nominal Cicilan (Rp)</label>
                        <input class="receivable-input" id="paymentAmount" type="text" inputmode="decimal"
                            name="nominal" data-format-number data-number-min="0.01" data-number-decimals="2"
                            value="{{ old('_form_context') === 'payment' ? old('nominal') : '' }}"
                            placeholder="Masukkan nominal" required>
                        <div class="receivable-max-hint" id="paymentMaxHint"></div>
                    </div>
                    <div class="receivable-form-group">
                        <label class="receivable-label" for="paymentMethod">Metode Pembayaran</label>
                        <select class="receivable-input" id="paymentMethod" name="metode" required>
                            <option value="Tunai" @selected(old('metode', 'Tunai') === 'Tunai')>Tunai</option>
                            <option value="Transfer" @selected(old('metode') === 'Transfer')>Transfer</option>
                            <option value="Lainnya" @selected(old('metode') === 'Lainnya')>Lainnya</option>
                        </select>
                    </div>
                    <div class="receivable-form-group">
                        <label class="receivable-label" for="paymentReceiver">Admin Penerima</label>
                        <input class="receivable-input receivable-readonly" id="paymentReceiver" value="{{ $adminName }}" readonly>
                    </div>
                    <div class="receivable-form-group">
                        <label class="receivable-label" for="paymentNote">Catatan</label>
                        <textarea class="receivable-input" id="paymentNote" name="catatan" rows="2" maxlength="5000" placeholder="Opsional">{{ old('_form_context') === 'payment' ? old('catatan') : '' }}</textarea>
                    </div>
                </div>
                <footer class="receivable-modal-footer">
                    <button type="button" class="receivable-btn receivable-btn-secondary" onclick="hideReceivableModal('paymentModal')">Batal</button>
                    <button type="submit" class="receivable-btn receivable-btn-green">Simpan Cicilan</button>
                </footer>
            </form>
        </div>
    </div>
</div>

<script>
const receivableBaseUrl = @json(url('/admin/receivables'));
const detailSummary = document.getElementById('receivableDetailSummary');
let activeReceivable = null;

function formatRupiah(value) {
    return Number(value || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 });
}

function escapeReceivableHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, character => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    })[character]);
}

function showReceivableModal(id) {
    const modal = document.getElementById(id);
    modal.classList.add('show');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('receivable-modal-open');
}

function hideReceivableModal(id) {
    const modal = document.getElementById(id);
    modal.classList.remove('show');
    modal.setAttribute('aria-hidden', 'true');
    if (!document.querySelector('.receivable-modal.show')) {
        document.body.classList.remove('receivable-modal-open');
    }
}

function resetDebtForm() {
    const form = document.getElementById('debtForm');
    form.reset();
    document.getElementById('debtDate').value = new Date().toLocaleDateString('en-CA');
}

function renderReceivableDetail(data) {
    activeReceivable = data;
    const balanceColor = Number(data.sisa_utang) > 0 ? '#dc3545' : '#198754';
    detailSummary.innerHTML = `
        <div><strong>No. Transaksi:</strong> ${escapeReceivableHtml(data.no_transaksi)}</div>
        <div><strong>Nama Pelanggan:</strong> ${escapeReceivableHtml(data.pelanggan)}</div>
        <div><strong>Tanggal Utang:</strong> ${escapeReceivableHtml(data.tanggal.split('-').reverse().join('/'))}</div>
        <div><strong>Admin Pemberi:</strong> ${escapeReceivableHtml(data.pemberi_pinjaman)}</div>
        <div><strong>Nominal Utang:</strong> Rp ${formatRupiah(data.nominal_awal)}</div>
        <div><strong>Total Pinjaman Tambahan:</strong> Rp ${formatRupiah(data.total_tambahan)}</div>
        <div><strong>Total Dibayar:</strong> <span class="receivable-green-text">Rp ${formatRupiah(data.total_dibayar)}</span></div>
        <div><strong>Sisa Utang:</strong> <span style="color:${balanceColor}">Rp ${formatRupiah(data.sisa_utang)}</span></div>
        <div><strong>Status:</strong> ${escapeReceivableHtml(data.status_label)}</div>
    `;

    const history = document.getElementById('receivableHistory');
    if (!data.transactions.length) {
        history.innerHTML = '<tr><td colspan="6" class="receivable-empty">Belum ada riwayat cicilan atau pinjaman tambahan.</td></tr>';
    } else {
        history.innerHTML = data.transactions.map(transaction => `
            <tr>
                <td>${escapeReceivableHtml(transaction.no_pembayaran)}</td>
                <td>${escapeReceivableHtml(transaction.tanggal)}</td>
                <td class="${transaction.jenis === 'payment' ? 'receivable-green-text' : ''}">
                    ${transaction.jenis === 'loan' ? '+' : '-'} Rp ${formatRupiah(transaction.nominal)}
                </td>
                <td>${escapeReceivableHtml(transaction.metode || '-')}</td>
                <td>${escapeReceivableHtml(transaction.admin_penerima)}</td>
                <td>${escapeReceivableHtml(transaction.catatan || '-')}</td>
            </tr>
        `).join('');
    }

    document.getElementById('openPaymentButton').disabled = Number(data.sisa_utang) <= 0;
    document.getElementById('openPaymentButton').style.opacity = Number(data.sisa_utang) <= 0 ? '.55' : '1';
}

async function openReceivableDetail(id) {
    detailSummary.innerHTML = '<div>Memuat detail piutang...</div>';
    document.getElementById('receivableHistory').innerHTML =
        '<tr><td colspan="6" class="receivable-empty">Memuat riwayat...</td></tr>';
    showReceivableModal('detailModal');

    try {
        const response = await fetch(receivableBaseUrl + '/' + id + '/json', {
            headers: { 'Accept': 'application/json' }
        });
        if (!response.ok) throw new Error('Gagal memuat detail piutang.');
        const result = await response.json();
        renderReceivableDetail(result.data);
    } catch (error) {
        detailSummary.innerHTML = '<div class="receivable-alert-danger">Detail piutang gagal dimuat. Silakan coba lagi.</div>';
        document.getElementById('receivableHistory').innerHTML =
            '<tr><td colspan="6" class="receivable-empty">Riwayat tidak dapat dimuat.</td></tr>';
    }
}

function openLoanForm() {
    if (!activeReceivable) return;
    const form = document.getElementById('loanForm');
    form.action = receivableBaseUrl + '/' + activeReceivable.id + '/loans';
    document.getElementById('loanReceivableId').value = activeReceivable.id;
    document.getElementById('loanCustomer').value = activeReceivable.pelanggan;
    document.getElementById('loanDate').value = new Date().toLocaleDateString('en-CA');
    hideReceivableModal('detailModal');
    showReceivableModal('loanModal');
}

function openPaymentForm() {
    if (!activeReceivable || Number(activeReceivable.sisa_utang) <= 0) return;
    const form = document.getElementById('paymentForm');
    form.action = receivableBaseUrl + '/' + activeReceivable.id + '/payments';
    document.getElementById('paymentReceivableId').value = activeReceivable.id;
    document.getElementById('paymentDate').value = new Date().toLocaleDateString('en-CA');
    document.getElementById('paymentAmount').max = activeReceivable.sisa_utang;
    document.getElementById('paymentAmount').value = '';
    document.getElementById('paymentMaxHint').textContent =
        'Maksimal bayar: Rp ' + formatRupiah(activeReceivable.sisa_utang);
    hideReceivableModal('detailModal');
    showReceivableModal('paymentModal');
}

document.querySelectorAll('.receivable-modal').forEach(modal => {
    modal.addEventListener('click', event => {
        if (event.target === modal) hideReceivableModal(modal.id);
    });
});
document.addEventListener('keydown', event => {
    if (event.key === 'Escape') {
        document.querySelectorAll('.receivable-modal.show').forEach(modal => hideReceivableModal(modal.id));
    }
});

@if($errors->any())
const oldContext = @json(old('_form_context'));
const oldReceivableId = @json(old('receivable_id'));
if (oldContext === 'debt') {
    showReceivableModal('debtModal');
} else if (oldReceivableId && ['loan', 'payment'].includes(oldContext)) {
    fetch(receivableBaseUrl + '/' + oldReceivableId + '/json', {
        headers: { 'Accept': 'application/json' }
    }).then(response => {
        if (!response.ok) throw new Error('Gagal memuat detail piutang.');
        return response.json();
    }).then(result => {
        renderReceivableDetail(result.data);
        if (oldContext === 'payment') {
            openPaymentForm();
            document.getElementById('paymentDate').value = @json(old('tanggal'));
            document.getElementById('paymentAmount').value = @json(old('nominal'));
            document.getElementById('paymentMethod').value = @json(old('metode', 'Tunai'));
            document.getElementById('paymentNote').value = @json(old('catatan', ''));
        } else {
            openLoanForm();
            document.getElementById('loanDate').value = @json(old('tanggal'));
            document.getElementById('loanAmount').value = @json(old('nominal'));
            document.getElementById('loanNote').value = @json(old('catatan', ''));
        }
    }).catch(() => showReceivableModal('detailModal'));
}
@endif
</script>
@endsection
