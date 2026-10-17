@extends('layouts.admin')

@section('title', 'Transaksi Pembelian (Petani) - Sumber Rezeki')

@section('content')
    <style>
        .page-header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .btn-add-purchase {
            background-color: #15803d;
            color: #ffffff;
            border: none;
            padding: 9px 18px;
            border-radius: 6px;
            font-size: 13.5px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-add-purchase:hover {
            background-color: #166534;
        }

        /* Table Styling matching screenshot */
        .table-container {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow-x: auto;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }

        .purchase-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            text-align: left;
        }

        .purchase-table th {
            background-color: #ffffff;
            color: #334155;
            font-weight: 700;
            padding: 14px 16px;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        .purchase-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
            vertical-align: middle;
            white-space: nowrap;
        }

        .purchase-table tr:hover td {
            background-color: #f8fafc;
        }

        /* Badge No Nota */
        .badge-nota {
            background-color: #475569;
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
            letter-spacing: 0.3px;
        }

        /* Action Buttons */
        .btn-action-edit {
            background-color: #eab308;
            color: #ffffff;
            border: none;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11.5px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: opacity 0.2s;
            text-decoration: none;
        }

        .btn-action-edit:hover {
            opacity: 0.9;
        }

        .btn-action-print {
            background-color: #06b6d4;
            color: #ffffff;
            border: none;
            padding: 4px 9px;
            border-radius: 4px;
            font-size: 11.5px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            transition: opacity 0.2s;
            text-decoration: none;
        }

        .btn-action-print:hover {
            opacity: 0.9;
        }

        /* MODAL STYLES (MATCHING SCREENSHOT 2 & 3) */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(2px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-dialog {
            background: #ffffff;
            width: 100%;
            max-width: 660px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: modalFadeIn 0.2s ease-out;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.96); }
            to { opacity: 1; transform: scale(1); }
        }

        .modal-header {
            background-color: #15803d;
            color: #ffffff;
            padding: 14px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 15px;
            font-weight: 700;
        }

        .modal-close-btn {
            background: none;
            border: none;
            color: #ffffff;
            font-size: 18px;
            cursor: pointer;
            opacity: 0.85;
            transition: opacity 0.2s;
        }

        .modal-close-btn:hover {
            opacity: 1;
        }

        .modal-body {
            padding: 22px 24px;
        }

        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 14px;
        }

        .form-grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 14px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
        }

        .form-input, .form-select {
            height: 38px;
            padding: 0 12px;
            font-size: 13px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            outline: none;
            color: #1e293b;
            transition: border-color 0.2s;
            width: 100%;
        }

        .form-input:focus, .form-select:focus {
            border-color: #16a34a;
            box-shadow: 0 0 0 2px rgba(22, 163, 74, 0.15);
        }

        .form-input[readonly] {
            background-color: #f8fafc;
            color: #64748b;
        }

        /* Green Highlight box for Netto 2 */
        .highlight-netto2-box {
            background-color: #15803d;
            color: #ffffff;
            border-radius: 6px;
            height: 38px;
            display: flex;
            align-items: center;
            padding: 0 14px;
            font-weight: 700;
            font-size: 13.5px;
        }

        /* Summary Total & Dibayar (Right-aligned text) */
        .modal-totals-summary {
            text-align: right;
            margin: 18px 0 20px 0;
        }

        .modal-totals-summary .summary-total {
            font-size: 12px;
            color: #475569;
            margin-bottom: 3px;
        }

        .modal-totals-summary .summary-dibayar {
            font-size: 17px;
            font-weight: 800;
            color: #15803d;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            border-top: 1px solid #f1f5f9;
            padding-top: 16px;
        }

        .btn-modal-cancel {
            background-color: #64748b;
            color: #ffffff;
            border: none;
            padding: 8px 18px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-modal-submit {
            background-color: #15803d;
            color: #ffffff;
            border: none;
            padding: 8px 18px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }

        .btn-modal-submit:hover {
            background-color: #166534;
        }

        @media (max-width: 600px) {
            .page-header-row {
                flex-wrap: wrap;
                gap: 10px;
            }

            .page-header-row .btn-add-purchase {
                width: 100%;
                justify-content: center;
            }

            .form-grid-2 {
                grid-template-columns: 1fr;
            }

            .form-grid-4 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .modal-body {
                padding: 16px 14px;
            }
        }
    </style>

    <!-- Header & Button Tambah -->
    <div class="page-header-row">
        <h1 class="page-heading" style="margin-bottom: 0;">Transaksi Pembelian</h1>
        <button type="button" class="btn-add-purchase" onclick="openCreateModal()">
            <i class="fa-solid fa-plus"></i>
            <span>Pembelian Baru</span>
        </button>
    </div>

    @if(session('success'))
        <div style="background-color: #dcfce7; border: 1px solid #86efac; color: #166534; padding: 12px 16px; border-radius: 8px; margin-bottom: 18px; font-size: 13.5px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Tabel Transaksi Pembelian -->
    <div class="table-container">
        <table class="purchase-table">
            <thead>
                <tr>
                    <th>No. Nota</th>
                    <th>Tanggal</th>
                    <th>Akun Pembayaran</th>
                    <th>Petani</th>
                    <th>Kategori</th>
                    <th>Netto 1 (Kg)</th>
                    <th>Netto 2 (Kg)</th>
                    <th>Total Bayar (Rp)</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $item)
                    <tr>
                        <td>
                            <span class="badge-nota">{{ $item->no_nota }}</span>
                        </td>
                        <td>{{ $item->tanggal->translatedFormat('d M Y') }}</td>
                        <td>{{ $item->cashAccount?->name ?? 'Kas Utama' }}</td>
                        <td><strong>{{ $item->petani }}</strong></td>
                        <td>{{ $item->kategori }}</td>
                        <td>{{ number_format($item->netto_1, $item->netto_1 == intval($item->netto_1) ? 0 : 1, ',', '.') }} Kg</td>
                        <td><strong>{{ number_format($item->netto_2, $item->netto_2 == intval($item->netto_2) ? 0 : 1, ',', '.') }} Kg</strong></td>
                        <td><strong style="color: #1e293b;">Rp {{ number_format($item->total_bayar, 0, ',', '.') }}</strong></td>
                        <td style="text-align: center;">
                            <div style="display: inline-flex; gap: 6px;">
                                <button type="button" class="btn-action-edit" onclick="openEditModal({{ $item->id }})">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </button>
                                <a href="{{ route('admin.purchases.print', $item->id) }}" target="_blank" class="btn-action-print" title="Print Struk Nota">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 30px; color: #64748b;">
                            Belum ada transaksi pembelian. Klik tombol "+ Pembelian Baru" untuk menambahkan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- MODAL FORM (UNTUK INPUT BARU & EDIT) -->
    <div class="modal-overlay" id="purchaseModal">
        <div class="modal-dialog">
            <div class="modal-header">
                <span class="modal-title" id="modalTitle">Transaksi Pembelian Baru</span>
                <button type="button" class="modal-close-btn" onclick="closeModal()">&times;</button>
            </div>

            <form id="purchaseForm" method="POST" action="{{ route('admin.purchases.store') }}">
                @csrf
                <div id="methodContainer"></div>

                <div class="modal-body">
                    <!-- Baris 1: Tanggal & Nama Petani -->
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="input_tanggal">Tanggal Transaksi</label>
                            <input type="date" name="tanggal" id="input_tanggal" class="form-input" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="input_petani">Nama Petani</label>
                            <input type="text" name="petani" id="input_petani" class="form-input" placeholder="Masukkan nama petani / supplier" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="input_cash_account">Dibayar dari Akun</label>
                        <select name="cash_account_id" id="input_cash_account" class="form-select" required>
                            @foreach($cashAccounts as $account)
                                <option value="{{ $account->id }}" @selected((string) old('cash_account_id', $cashAccounts->firstWhere('type', 'cash')?->id) === (string) $account->id)>
                                    {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Baris 2: Kategori & Harga Beli -->
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="input_kategori">Kategori Barang</label>
                            <select name="kategori" id="input_kategori" class="form-select" required>
                                <option value="TBS">TBS</option>
                                <option value="Brondolan">Brondolan</option>
                                <option value="BRD Afkir">BRD Afkir</option>
                                <option value="Buah Kecil">Buah Kecil</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="input_harga">Harga Beli per Kg (Rp)</label>
                            <input type="text" inputmode="decimal" name="harga_per_kg" id="input_harga" class="form-input"
                                data-format-number data-number-decimals="2" placeholder="Contoh: 2.530,60" required>
                        </div>
                    </div>

                    <!-- Baris 3: Bruto, Tara, Netto 1, Rafaksi -->
                    <div class="form-grid-4">
                        <div class="form-group">
                            <label for="input_bruto">Bruto (Kg)</label>
                            <input type="text" inputmode="numeric" name="bruto" id="input_bruto" class="form-input"
                                data-format-number data-number-integer placeholder="0" required>
                        </div>
                        <div class="form-group">
                            <label for="input_tara">Tara (Kg)</label>
                            <input type="text" inputmode="decimal" name="tara" id="input_tara" class="form-input" data-format-number placeholder="0" required>
                        </div>
                        <div class="form-group">
                            <label for="input_netto_1">Netto 1 (Kg)</label>
                            <input type="text" id="display_netto_1" class="form-input" value="0,00" readonly>
                            <input type="hidden" name="netto_1" id="input_netto_1" value="0">
                        </div>
                        <div class="form-group">
                            <label for="input_rafaksi">Rafaksi (%)</label>
                            <input type="text" inputmode="decimal" name="rafaksi" id="input_rafaksi" class="form-input"
                                data-format-number data-number-min="0" data-number-decimals="1"
                                value="{{ old('rafaksi') }}" placeholder="0">
                        </div>
                    </div>

                    <!-- Baris 4: Netto 2 & Upah Bongkar -->
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label>Netto 2 (Kg) - Dasar Bayar</label>
                            <div class="highlight-netto2-box" id="display_netto_2">0,00</div>
                            <input type="hidden" name="netto_2" id="input_netto_2" value="0">
                        </div>
                        <div class="form-group">
                            <label for="input_upah_bongkar">Upah Bongkar (Rp)</label>
                            <input type="text" inputmode="numeric" name="upah_bongkar" id="input_upah_bongkar"
                                class="form-input" data-format-number data-number-integer data-number-min="0"
                                data-number-max="9999999999" value="{{ old('upah_bongkar') }}" placeholder="0">
                        </div>
                    </div>

                    <!-- Hidden inputs untuk total pembelian & bayar -->
                    <input type="hidden" name="total_pembelian" id="input_total_pembelian" value="0">
                    <input type="hidden" name="total_bayar" id="input_total_bayar" value="0">

                    <!-- Ringkasan Total & Dibayar -->
                    <div class="modal-totals-summary">
                        <div class="summary-total" id="summaryTotalText">Total Pembelian: Rp 0</div>
                        <div class="summary-dibayar" id="summaryDibayarText">Dibayar: Rp 0</div>
                    </div>

                    <!-- Tombol Aksi Batal & Simpan -->
                    <div class="modal-footer">
                        <button type="button" class="btn-modal-cancel" onclick="closeModal()">Batal</button>
                        <button type="submit" class="btn-modal-submit">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span>Simpan Transaksi</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    const modal = document.getElementById('purchaseModal');
    const form = document.getElementById('purchaseForm');
    const modalTitle = document.getElementById('modalTitle');
    const methodContainer = document.getElementById('methodContainer');

    const inputTanggal = document.getElementById('input_tanggal');
    const inputPetani = document.getElementById('input_petani');
    const inputKategori = document.getElementById('input_kategori');
    const inputHarga = document.getElementById('input_harga');
    const inputBruto = document.getElementById('input_bruto');
    const inputTara = document.getElementById('input_tara');
    const inputRafaksi = document.getElementById('input_rafaksi');
    const inputUpahBongkar = document.getElementById('input_upah_bongkar');

    const displayNetto1 = document.getElementById('display_netto_1');
    const inputNetto1 = document.getElementById('input_netto_1');

    const displayNetto2 = document.getElementById('display_netto_2');
    const inputNetto2 = document.getElementById('input_netto_2');

    const inputTotalPembelian = document.getElementById('input_total_pembelian');
    const inputTotalBayar = document.getElementById('input_total_bayar');

    const summaryTotalText = document.getElementById('summaryTotalText');
    const summaryDibayarText = document.getElementById('summaryDibayarText');

    // Kalkulasi otomatis Timbangan, Rafaksi, dan Total Pembayaran
    function calculate() {
        const bruto = window.parseFormattedNumber(inputBruto.value);
        const tara = window.parseFormattedNumber(inputTara.value);
        const harga = window.parseFormattedNumber(inputHarga.value);
        const rafaksi = window.parseFormattedNumber(inputRafaksi.value);
        const upahBongkar = window.parseFormattedNumber(inputUpahBongkar.value);

        // Netto 1 = Bruto - Tara
        let netto1 = Math.max(0, bruto - tara);
        
        // Netto 2 = Netto 1 - (Netto 1 * Rafaksi / 100)
        let potongan = netto1 * (rafaksi / 100);
        let netto2 = Math.max(0, netto1 - potongan);

        // Total Pembelian = Netto 2 * Harga Beli
        let totalPembelian = Math.round(netto2 * harga);
        
        // Dibayar = Total Pembelian - Upah Bongkar
        let dibayar = totalPembelian - upahBongkar;

        displayNetto1.value = formatNumber(netto1);
        inputNetto1.value = netto1.toFixed(2);

        displayNetto2.innerText = formatNumber(netto2);
        inputNetto2.value = netto2.toFixed(2);

        inputTotalPembelian.value = totalPembelian;
        inputTotalBayar.value = dibayar;

        summaryTotalText.innerText = 'Total Pembelian: Rp ' + formatRupiah(totalPembelian);
        summaryDibayarText.innerText = 'Dibayar: Rp ' + formatRupiah(dibayar);
    }

    function formatNumber(num) {
        return num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    function formatRupiah(num) {
        return num.toLocaleString('id-ID');
    }

    // Pasang Event Listeners untuk hitung otomatis saat input diketik
    [inputBruto, inputTara, inputHarga, inputRafaksi, inputUpahBongkar].forEach(el => {
        el.addEventListener('input', calculate);
    });

    // Buka Modal Tambah Baru
    function openCreateModal() {
        modalTitle.innerText = 'Transaksi Pembelian Baru';
        form.action = "{{ route('admin.purchases.store') }}";
        methodContainer.innerHTML = '';
        form.reset();

        inputTanggal.value = new Date().toISOString().split('T')[0];
        inputRafaksi.value = '';
        inputUpahBongkar.value = '';
        [inputHarga, inputBruto, inputTara, inputRafaksi, inputUpahBongkar]
            .forEach(input => window.formatNumberInput(input));
        calculate();

        modal.classList.add('active');
    }

    // Buka Modal Edit Data
    function openEditModal(id) {
        fetch(`/admin/purchases/${id}/json`)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    const data = res.data;
                    modalTitle.innerText = `Edit Transaksi Pembelian: ${data.no_nota}`;
                    form.action = `/admin/purchases/${id}`;
                    methodContainer.innerHTML = '@method("PUT")';

                    // Isi formulir
                    inputTanggal.value = data.tanggal ? data.tanggal.split('T')[0] : '';
                    document.getElementById('input_cash_account').value = data.cash_account_id;
                    inputPetani.value = data.petani;
                    inputKategori.value = data.kategori;
                    inputHarga.value = data.harga_per_kg;
                    inputBruto.value = data.bruto;
                    inputTara.value = data.tara;
                    inputRafaksi.value = Number(data.rafaksi) ? data.rafaksi : '';
                    inputUpahBongkar.value = Number(data.upah_bongkar) ? data.upah_bongkar : '';
                    [inputHarga, inputBruto, inputTara, inputRafaksi, inputUpahBongkar]
                        .forEach(input => window.formatNumberInput(input));

                    calculate();
                    modal.classList.add('active');
                }
            })
            .catch(err => {
                alert('Gagal mengambil data transaksi.');
            });
    }

    function closeModal() {
        modal.classList.remove('active');
    }

    // Klik di luar dialog untuk menutup
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });
</script>
@endsection
