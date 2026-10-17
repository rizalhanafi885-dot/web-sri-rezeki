@extends('layouts.admin')

@section('title', 'Stok Opname - Sumber Rezeki')

@section('content')
<style>
    .main-wrapper { min-width: 0; }
    .container-fluid { width: 100%; min-width: 0; }
    .opname-heading-row { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 20px; }
    .opname-heading { margin: 0; font-size: 20px; }
    .opname-card { overflow: hidden; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; box-shadow: 0 1px 3px rgba(0, 0, 0, .05); }
    .opname-table-wrap { max-width: 100%; overflow-x: auto; padding: 12px; }
    .opname-table { width: 100%; border-collapse: collapse; font-size: 13px; text-align: left; }
    .opname-table th, .opname-table td { padding: 10px 12px; border-bottom: 1px solid #dbe2ea; white-space: nowrap; }
    .opname-table thead th { background: #f8fafc; font-weight: 700; }
    .opname-table tbody tr:hover { background: #f8fafc; }
    .opname-table tbody th { font-weight: 700; }
    .opname-difference { font-weight: 700; }
    .difference-negative { color: #dc3545; }
    .difference-positive { color: #198754; }
    .opname-actions { text-align: right; }
    .opname-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; border: 0; border-radius: 5px; padding: 8px 12px; font: inherit; font-size: 13px; text-decoration: none; cursor: pointer; }
    .opname-btn-primary { color: #212529; background: #ffc107; }
    .opname-btn-edit { color: #212529; background: #ffc107; padding: 6px 10px; }
    .opname-btn-secondary { color: #fff; background: #64748b; }
    .opname-btn:hover { filter: brightness(.95); }
    .opname-empty { color: #64748b; text-align: center !important; }
    .opname-alert { margin-bottom: 16px; border-radius: 6px; padding: 12px 14px; }
    .opname-alert-success { background: #dcfce7; color: #166534; }
    .opname-alert-danger { background: #fee2e2; color: #991b1b; }
    .opname-modal { position: fixed; inset: 0; z-index: 200; display: none; align-items: flex-start; justify-content: center; overflow-y: auto; padding: 8vh 16px 24px; background: rgba(15, 23, 42, .55); }
    .opname-modal.show { display: flex; }
    .opname-modal-dialog { width: min(560px, 100%); margin: auto; }
    .opname-modal-content { overflow: hidden; border-radius: 8px; background: #fff; box-shadow: 0 20px 50px rgba(15, 23, 42, .25); }
    .opname-modal-header, .opname-modal-footer { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; }
    .opname-modal-header { background: #ffc107; }
    .opname-modal-title { margin: 0; font-size: 17px; font-weight: 600; }
    .opname-close { border: 0; background: transparent; color: #334155; font-size: 24px; cursor: pointer; }
    .opname-modal-body { padding: 18px 16px; }
    .opname-modal-footer { justify-content: flex-end; gap: 8px; border-top: 1px solid #e2e8f0; }
    .opname-form-group { margin-bottom: 16px; }
    .opname-label { display: block; margin-bottom: 6px; color: #334155; font-size: 13px; }
    .opname-control { width: 100%; min-height: 38px; border: 1px solid #cbd5e1; border-radius: 5px; padding: 8px 10px; font: inherit; font-size: 13px; }
    .opname-control:focus { outline: 2px solid rgba(22, 163, 74, .18); border-color: #16a34a; }
    .opname-readonly { background: #f8fafc; color: #475569; }
    .opname-calculation { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin: 18px 0; }
    .opname-calculation div { border-radius: 6px; background: #f8fafc; padding: 12px; }
    .opname-calculation span { display: block; margin-bottom: 5px; color: #64748b; font-size: 12px; }
    .opname-calculation strong { font-size: 16px; }
    body.opname-modal-open { overflow: hidden; }
    @media (max-width: 700px) {
        .opname-heading { font-size: 18px; }
        .opname-heading-row { align-items: flex-start; }
        .opname-table-wrap { padding: 8px; }
        .opname-table th, .opname-table td { padding: 9px 10px; }
        .opname-modal { padding: 12px; }
    }
</style>

<div class="container-fluid">
    <div class="opname-heading-row">
        <h1 class="opname-heading">Stok Opname (Penyesuaian)</h1>
        <button class="opname-btn opname-btn-primary" type="button" onclick="resetOpnameForm(); showOpnameModal()">
            Buat Opname
        </button>
    </div>

    @if(session('success'))
        <div class="opname-alert opname-alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="opname-alert opname-alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <section class="opname-card" aria-label="Riwayat stok opname">
        <div class="opname-table-wrap">
            <table class="opname-table">
                <thead>
                    <tr>
                        <th scope="col">Tanggal</th>
                        <th scope="col">Kategori</th>
                        <th scope="col">Stok Sistem (Kg)</th>
                        <th scope="col">Stok Fisik (Kg)</th>
                        <th scope="col">Selisih (Kg)</th>
                        <th scope="col">Petugas</th>
                        <th scope="col" aria-label="Aksi"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($opnames as $opname)
                        <tr>
                            <td>{{ $opname->tanggal->format('d M Y') }}</td>
                            <th scope="row">{{ $opname->kategori }}</th>
                            <td>{{ number_format($opname->stok_sistem_kg, 0, ',', '.') }} Kg</td>
                            <td>{{ number_format($opname->stok_fisik_kg, 0, ',', '.') }} Kg</td>
                            <td class="opname-difference {{ $opname->selisih_kg < 0 ? 'difference-negative' : ($opname->selisih_kg > 0 ? 'difference-positive' : '') }}">
                                {{ number_format($opname->selisih_kg, 0, ',', '.') }} Kg
                            </td>
                            <td>{{ $opname->petugas }}</td>
                            <td class="opname-actions">
                                <button type="button" class="opname-btn opname-btn-edit"
                                    onclick="editOpname({{ Js::from([
                                        'id' => $opname->id,
                                        'tanggal' => $opname->tanggal->format('Y-m-d'),
                                        'kategori' => $opname->kategori,
                                        'stok_sistem_kg' => $opname->stok_sistem_kg,
                                        'stok_fisik_kg' => $opname->stok_fisik_kg,
                                        'selisih_kg' => $opname->selisih_kg,
                                    ]) }})">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="opname-empty">Belum ada data stok opname.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<div class="opname-modal" id="opnameModal" aria-hidden="true">
    <div class="opname-modal-dialog">
        <div class="opname-modal-content">
            <form id="opnameForm" action="{{ route('admin.stock-opnames.store') }}" method="POST">
                @csrf
                <div id="opnameMethodField">
                    @if(old('_method') === 'PUT')
                        <input type="hidden" name="_method" value="PUT">
                        <input type="hidden" name="_opname_id" value="{{ old('_opname_id') }}">
                    @endif
                </div>
                <header class="opname-modal-header">
                    <h2 class="opname-modal-title" id="opnameModalTitle">Buat Stok Opname</h2>
                    <button type="button" class="opname-close" aria-label="Tutup" onclick="hideOpnameModal()">×</button>
                </header>
                <div class="opname-modal-body">
                    <div class="opname-form-group">
                        <label class="opname-label" for="opnameTanggal">Tanggal</label>
                        <input class="opname-control" id="opnameTanggal" type="date" name="tanggal"
                            value="{{ old('tanggal', now()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="opname-form-group">
                        <label class="opname-label" for="opnameKategori">Kategori</label>
                        <select class="opname-control" id="opnameKategori" name="kategori" required>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" @selected(old('kategori') === $category)>{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="opname-calculation">
                        <div>
                            <span>Stok Sistem</span>
                            <strong><span id="opnameSystemStock">0</span> Kg</strong>
                        </div>
                        <div>
                            <span>Selisih</span>
                            <strong><span id="opnameDifference">0</span> Kg</strong>
                        </div>
                    </div>
                    <div class="opname-form-group">
                        <label class="opname-label" for="opnamePhysicalStock">Stok Fisik (Kg)</label>
                        <input class="opname-control" id="opnamePhysicalStock" type="text" inputmode="decimal"
                            name="stok_fisik_kg" data-format-number data-number-min="0" data-number-decimals="2"
                            data-number-max="9999999999.99"
                            value="{{ old('stok_fisik_kg', '0') }}" required>
                    </div>
                </div>
                <footer class="opname-modal-footer">
                    <button type="button" class="opname-btn opname-btn-secondary" onclick="hideOpnameModal()">Batal</button>
                    <button type="submit" class="opname-btn opname-btn-primary">Simpan Opname</button>
                </footer>
            </form>
        </div>
    </div>
</div>

<script>
const opnameStocks = @json($stocks);
const opnameRecords = @json($opnameRecords);
const opnameForm = document.getElementById('opnameForm');
const opnameCategory = document.getElementById('opnameKategori');
const opnamePhysical = document.getElementById('opnamePhysicalStock');
let editingOpname = null;

function formatOpnameKg(value) {
    return Number(value || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 });
}

function updateOpnameCalculation() {
    let systemStock = Number(opnameStocks[opnameCategory.value] || 0);
    if (editingOpname && editingOpname.kategori === opnameCategory.value) {
        systemStock -= Number(editingOpname.selisih_kg || 0);
    }
    const difference = window.parseFormattedNumber(opnamePhysical.value) - systemStock;
    document.getElementById('opnameSystemStock').textContent = formatOpnameKg(systemStock);
    document.getElementById('opnameDifference').textContent = formatOpnameKg(difference);
}

function showOpnameModal() {
    const modal = document.getElementById('opnameModal');
    modal.classList.add('show');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('opname-modal-open');
}

function hideOpnameModal() {
    const modal = document.getElementById('opnameModal');
    modal.classList.remove('show');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('opname-modal-open');
    editingOpname = null;
}

function resetOpnameForm() {
    opnameForm.reset();
    opnameForm.action = @json(route('admin.stock-opnames.store'));
    document.getElementById('opnameMethodField').innerHTML = '';
    document.getElementById('opnameTanggal').value = new Date().toLocaleDateString('en-CA');
    document.getElementById('opnameModalTitle').textContent = 'Buat Stok Opname';
    opnameCategory.selectedIndex = 0;
    opnamePhysical.value = '0';
    window.formatNumberInput(opnamePhysical);
    editingOpname = null;
    updateOpnameCalculation();
}

function editOpname(data) {
    editingOpname = data;
    opnameForm.action = @json(url('/admin/stock-opnames')) + '/' + data.id;
    document.getElementById('opnameMethodField').innerHTML =
        '<input type="hidden" name="_method" value="PUT">' +
        '<input type="hidden" name="_opname_id" value="' + data.id + '">';
    document.getElementById('opnameTanggal').value = data.tanggal;
    opnameCategory.value = data.kategori;
    opnamePhysical.value = data.stok_fisik_kg;
    window.formatNumberInput(opnamePhysical);
    document.getElementById('opnameModalTitle').textContent = 'Edit Stok Opname';
    updateOpnameCalculation();
    showOpnameModal();
}

opnameCategory.addEventListener('change', updateOpnameCalculation);
opnamePhysical.addEventListener('input', updateOpnameCalculation);
document.getElementById('opnameModal').addEventListener('click', event => {
    if (event.target.id === 'opnameModal') hideOpnameModal();
});
document.addEventListener('keydown', event => {
    if (event.key === 'Escape') hideOpnameModal();
});

@if($errors->any())
const oldOpnameId = @json(old('_opname_id'));
if (oldOpnameId) {
    editingOpname = opnameRecords[oldOpnameId] || null;
    opnameForm.action = @json(url('/admin/stock-opnames')) + '/' + oldOpnameId;
    document.getElementById('opnameModalTitle').textContent = 'Edit Stok Opname';
}
showOpnameModal();
@endif
updateOpnameCalculation();
</script>
@endsection
