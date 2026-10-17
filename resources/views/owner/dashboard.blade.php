@extends('layouts.app')

@php
    $pageTitles = [
        'dashboard' => 'Dashboard Owner',
        'cash' => 'Kas / Saldo',
        'sales' => 'Penjualan',
        'expenses' => 'Pengeluaran / Operasional',
        'reports' => 'Neraca & Laba Rugi',
    ];
@endphp

@section('title', $pageTitles[$page] ?? 'Dashboard Owner')
@section('page-title', $pageTitles[$page] ?? 'Dashboard Owner')

@section('sidebar-menu')
    <div class="nav-label">Menu Owner</div>
    <a href="{{ route('owner.dashboard') }}" class="nav-item {{ $page === 'dashboard' ? 'active' : '' }}"><i class="fa-solid fa-house"></i><span>Dashboard Owner</span></a>
    <a href="{{ route('owner.cash') }}" class="nav-item {{ $page === 'cash' ? 'active' : '' }}"><i class="fa-solid fa-wallet"></i><span>Kas / Saldo</span></a>
    <a href="{{ route('owner.sales') }}" class="nav-item {{ $page === 'sales' ? 'active' : '' }}"><i class="fa-solid fa-chart-line"></i><span>Penjualan</span></a>
    <a href="{{ route('owner.expenses') }}" class="nav-item {{ $page === 'expenses' ? 'active' : '' }}"><i class="fa-solid fa-money-bill-transfer"></i><span>Pengeluaran / Operasional</span></a>
    <a href="{{ route('owner.reports') }}" class="nav-item {{ $page === 'reports' ? 'active' : '' }}"><i class="fa-solid fa-file-invoice-dollar"></i><span>Neraca &amp; Laba Rugi</span></a>
@endsection

@section('content')
    @if($errors->any())
        <div class="card" style="margin-bottom: 18px; color: #991b1b; background: #fef2f2;">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    @if($page === 'dashboard')
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-info"><span>Total Kas &amp; Bank</span><h3>Rp {{ number_format($totalCashBalance, 0, ',', '.') }}</h3></div>
            <div class="stat-icon icon-green"><i class="fa-solid fa-wallet"></i></div>
        </div>
        <div class="stat-card">
            <div class="stat-info"><span>Penjualan Hari Ini</span><h3>Rp {{ number_format($todaySummary['revenue'], 0, ',', '.') }}</h3></div>
            <div class="stat-icon icon-blue"><i class="fa-solid fa-arrow-trend-up"></i></div>
        </div>
        <div class="stat-card">
            <div class="stat-info"><span>Pengeluaran Operasional Periode</span><h3>Rp {{ number_format($periodSummary['operational_expenses'], 0, ',', '.') }}</h3></div>
            <div class="stat-icon icon-amber"><i class="fa-solid fa-receipt"></i></div>
        </div>
        <div class="stat-card">
            <div class="stat-info"><span>Laba / (Rugi) Periode</span><h3>Rp {{ number_format($periodSummary['net_profit'], 0, ',', '.') }}</h3></div>
            <div class="stat-icon icon-purple"><i class="fa-solid fa-sack-dollar"></i></div>
        </div>
    </div>
    <div class="card" style="margin-top:22px;">
        <div class="card-header"><h2>Ringkasan</h2></div>
        <p>Gunakan menu di samping untuk membuka Kas / Saldo, Penjualan, Pengeluaran / Operasional, atau Laporan secara terpisah.</p>
    </div>
    @endif

    @if($page === 'cash')
    <section class="cash-page" id="saldo">
        <section class="cash-section" aria-labelledby="cashAccountsTitle">
            <div class="cash-section-heading">
                <h2 id="cashAccountsTitle">Ringkasan Saldo</h2>
                <p>Saldo awal dan rekening di sini khusus Owner. Saldo Admin hanya bertambah saat dana ditransfer ke KAS RAM.</p>
            </div>
            <div class="cash-table-wrap">
                <table class="custom-table cash-table">
                    <thead><tr><th>Akun</th><th>Jenis</th><th>Saldo Saat Ini</th></tr></thead>
                    <tbody>
                        @foreach($accounts as $account)
                            <tr>
                                <td><strong>{{ $account->name }}</strong></td>
                                <td>{{ $account->type === 'cash' ? 'Kas' : 'Bank' }}</td>
                                <td>Rp {{ number_format($account->current_balance, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        <tr class="cash-total-row">
                            <td colspan="2">Total saldo seluruh akun</td>
                            <td>Rp {{ number_format($totalCashBalance, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <div class="cash-actions-grid">
            <form action="{{ route('owner.cash.balance.store') }}" method="POST" class="card cash-form-card">
                @csrf
                <h3>Tambah Saldo Kas Utama</h3>
                <p class="cash-form-hint">Saldo ini hanya menambah Kas Utama Owner. Saldo Admin tidak berubah sebelum transfer.</p>
                <div class="owner-form-field">
                    <label for="ownerCashDate">Tanggal</label>
                    <input id="ownerCashDate" name="tanggal" type="date" value="{{ now()->format('Y-m-d') }}"
                        required class="owner-form-control">
                </div>
                <div class="owner-form-field">
                    <label for="ownerCashBalance">Nominal tambahan (Rp)</label>
                    <input id="ownerCashBalance" name="nominal" type="text" inputmode="numeric"
                        data-money-integer data-money-min="1" data-money-max="999999999999"
                        placeholder="Contoh: 1.000.000" required class="owner-form-control">
                </div>
                <div class="owner-form-field">
                    <label for="ownerCashNote">Keterangan (opsional)</label>
                    <input id="ownerCashNote" name="keterangan" maxlength="5000" class="owner-form-control">
                </div>
                <button type="submit" class="owner-action-button">Tambah Saldo Kas</button>
            </form>

            <form action="{{ route('owner.accounts.store') }}" method="POST" class="card cash-form-card">
                @csrf
                <h3>Tambah Rekening Bank</h3>
                <p class="cash-form-hint">Saldo awal rekening ini hanya menambah saldo Owner, tidak mengubah saldo Admin.</p>
                <div class="owner-form-field">
                    <label for="accountName">Nama akun / rekening</label>
                <input id="accountName" name="name" maxlength="255" required placeholder="Contoh: BCA - Operasional"
                        class="owner-form-control">
                </div>
                <div class="owner-form-field">
                    <label for="accountBalance">Saldo awal (Rp)</label>
                <input id="accountBalance" name="initial_balance" type="text" inputmode="numeric" value="0" required
                    data-money-integer data-money-min="0" data-money-max="999999999999"
                        class="owner-form-control">
                </div>
                <button type="submit" class="owner-action-button">Simpan Rekening</button>
            </form>

            <form action="{{ route('owner.transfers.store') }}" method="POST" class="card cash-form-card">
                @csrf
                <h3>Transfer ke Kas RAM Admin</h3>
                <p class="cash-form-hint">Transfer mengurangi Kas Utama Owner dan menambah saldo KAS RAM Admin.</p>
                <div class="owner-form-field">
                    <label for="transferDate">Tanggal</label>
                <input id="transferDate" name="tanggal" type="date" value="{{ now()->format('Y-m-d') }}" required
                        class="owner-form-control">
                </div>
                <div class="owner-form-field">
                    <label for="transferSource">Dari akun Owner</label>
                    <input id="transferSource" type="text" value="{{ $mainCashAccount->name }}" readonly class="owner-form-control">
                </div>
                <div class="owner-form-field">
                    <label for="transferDestination">Ke akun Admin</label>
                    <select id="transferDestination" name="destination_account_id" required class="owner-form-control">
                        @foreach($transferDestinationAccounts as $account)<option value="{{ $account->id }}">{{ $account->name }}</option>@endforeach
                    </select>
                </div>
                <div class="owner-form-field">
                    <label for="transferAmount">Nominal (Rp)</label>
                <input id="transferAmount" name="nominal" type="text" inputmode="numeric"
                    data-money-integer data-money-min="1" data-money-max="999999999999"
                    placeholder="Contoh: 1.000.000" required
                        class="owner-form-control">
                </div>
                <div class="owner-form-field">
                    <label for="transferNote">Keterangan</label>
                <input id="transferNote" name="keterangan" maxlength="5000" class="owner-form-control">
                </div>
                @if($transferDestinationAccounts->isEmpty())
                    <p class="cash-form-hint">Akun KAS RAM Admin belum tersedia sehingga transfer belum dapat dilakukan.</p>
                @endif
                <button type="submit" class="owner-action-button" @disabled($transferDestinationAccounts->isEmpty())>Catat Transfer</button>
            </form>
        </div>

        <section class="transfer-history" aria-labelledby="transferHistoryTitle">
            <h3 id="transferHistoryTitle">Riwayat Transfer Terbaru</h3>
            <div class="cash-table-wrap">
                <table class="custom-table cash-table">
                    <thead><tr><th>No. Transaksi</th><th>Tanggal</th><th>Dari</th><th>Ke</th><th>Nominal</th><th>Keterangan</th></tr></thead>
                    <tbody>
                        @forelse($transfers as $transfer)
                            <tr>
                                <td>{{ $transfer->no_transaksi }}</td><td>{{ $transfer->tanggal->format('d/m/Y') }}</td>
                                <td>{{ $transfer->sourceAccount->name }}</td><td>{{ $transfer->destinationAccount->name }}</td>
                                <td>Rp {{ number_format($transfer->nominal, 0, ',', '.') }}</td><td>{{ $transfer->keterangan ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6">Belum ada transfer.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </section>
    @endif

    @if($page === 'sales')
    <div class="sales-page" id="penjualan">
        <section class="sales-section">
            <div class="sales-section-heading">
                <h2>Ringkasan Penjualan</h2>
                <p>Penjualan dicatat melalui Pengiriman PKS agar pendapatan dan stok tidak tercatat dua kali.</p>
            </div>
            <div class="sales-summary-grid">
                <div class="sales-summary-card">
                    <div class="sales-summary-label">Penjualan hari ini</div>
                    <div class="sales-summary-value">Rp {{ number_format($todaySummary['revenue'], 0, ',', '.') }}</div>
                    <div class="sales-summary-detail">{{ number_format($todaySummary['tonnage'], 2, ',', '.') }} Kg terjual</div>
                </div>
                <div class="sales-summary-card">
                    <div class="sales-summary-label">Penjualan periode dipilih</div>
                    <div class="sales-summary-value">Rp {{ number_format($periodSummary['revenue'], 0, ',', '.') }}</div>
                    <div class="sales-summary-detail">{{ number_format($periodSummary['tonnage'], 2, ',', '.') }} Kg terjual</div>
                </div>
            </div>
            <div class="sales-subsection">
                <h3>Rincian Penjualan Hari Ini</h3>
                <div class="sales-table-wrap">
                    <table class="custom-table sales-table">
                        <thead><tr><th>No. Nota</th><th>PKS</th><th>Tonase</th><th>Harga/Kg</th><th>Pendapatan</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($salesToday as $sale)
                                <tr><td>{{ $sale->no_nota }}</td><td>{{ $sale->pks_tujuan }}</td><td>{{ number_format($sale->total_kg, 2, ',', '.') }} Kg</td><td>Rp {{ number_format($sale->harga_jual_per_kg, 0, ',', '.') }}</td><td>Rp {{ number_format($sale->total_pendapatan, 0, ',', '.') }}</td><td>{{ $sale->is_paid ? 'Lunas' : 'Belum dibayar' }}</td></tr>
                            @empty
                                <tr><td colspan="6">Belum ada penjualan hari ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
        <section class="sales-section">
            <div class="sales-section-heading">
                <h2>Riwayat Penjualan</h2>
                <p>Pilih rentang tanggal untuk melihat rincian penjualan pada periode tertentu.</p>
            </div>
        @if($salesPeriod->contains(fn ($sale) => (float) $sale->harga_jual_per_kg <= 0))
            <p class="sales-warning">Ada pengiriman lama tanpa harga jual tersimpan; pendapatannya belum dihitung. Perbarui harga pada data Pengiriman PKS agar laporan periode mencerminkan nilai sebenarnya.</p>
        @endif
        <form method="GET" action="{{ route('owner.sales') }}" class="sales-period-form">
            <div class="owner-form-field"><label for="periodFrom">Dari tanggal</label><input id="periodFrom" class="owner-form-control" type="date" name="from" value="{{ $period['from'] }}" required></div>
            <div class="owner-form-field"><label for="periodTo">Sampai tanggal</label><input id="periodTo" class="owner-form-control" type="date" name="to" value="{{ $period['to'] }}" required></div>
            <button type="submit" class="owner-action-button">Tampilkan Periode</button>
        </form>
        <div class="sales-table-wrap">
            <table class="custom-table sales-table">
                <thead><tr><th>No. Nota</th><th>Tanggal</th><th>PKS</th><th>Tonase</th><th>Harga/Kg</th><th>Pendapatan</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($salesPeriod as $sale)
                        <tr><td>{{ $sale->no_nota }}</td><td>{{ $sale->tanggal->format('d/m/Y') }}</td><td>{{ $sale->pks_tujuan }}</td><td>{{ number_format($sale->total_kg, 2, ',', '.') }} Kg</td><td>Rp {{ number_format($sale->harga_jual_per_kg, 0, ',', '.') }}</td><td>Rp {{ number_format($sale->total_pendapatan, 0, ',', '.') }}</td><td>{{ $sale->is_paid ? 'Lunas' : 'Belum dibayar' }}</td></tr>
                    @empty
                        <tr><td colspan="7">Belum ada penjualan pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        </section>
        <section class="sales-section">
            <div class="sales-section-heading">
                <h2>Form Input Penjualan</h2>
                <p>Catat pengiriman dan rincian muatan untuk membuat transaksi penjualan baru.</p>
            </div>
        <form action="{{ route('owner.sales.store') }}" method="POST">
            @csrf
            <div class="sales-form-grid">
                <div class="owner-form-field"><label for="saleDate">Tanggal</label><input id="saleDate" class="owner-form-control" name="tanggal" type="date" value="{{ now()->format('Y-m-d') }}" required></div>
                <div class="owner-form-field"><label for="salePks">PKS tujuan</label><input id="salePks" class="owner-form-control" name="pks_tujuan" maxlength="255" required></div>
                <div class="owner-form-field"><label for="saleNopol">Nopol kendaraan</label><input id="saleNopol" class="owner-form-control" name="nopol" maxlength="30" required></div>
                <div class="owner-form-field"><label for="saleDriver">Nama driver</label><input id="saleDriver" class="owner-form-control" name="driver" maxlength="100" required></div>
                <div class="owner-form-field"><label for="salePrice">Harga jual per Kg (Rp)</label><input id="salePrice" class="owner-form-control" name="harga_jual_per_kg" type="text" inputmode="numeric" data-money-integer data-money-min="1" data-money-max="999999" placeholder="Contoh: 2.950" required></div>
                @foreach(\App\Models\Shipment::CARGO_FIELDS as $category => $field)
                    <div class="owner-form-field"><label for="sale-{{ $field }}">{{ $category }} (Kg)</label><input id="sale-{{ $field }}" class="owner-form-control" name="{{ $field }}" type="number" min="0" max="9999999999.99" step="0.01" value="0" required></div>
                @endforeach
                <div class="owner-form-field sales-form-wide"><label for="saleNote">Catatan (opsional)</label><textarea id="saleNote" class="owner-form-control" name="catatan" rows="2"></textarea></div>
            </div>
            <div class="sales-form-actions"><button type="submit" class="owner-action-button">Simpan Penjualan</button></div>
        </form>
        </section>
        <section class="sales-section">
            <div class="sales-section-heading">
                <h2>Catat Pelunasan</h2>
                <p>Pelunasan dicatat satu kali untuk seluruh nilai penjualan; saldo bertambah pada akun yang dipilih.</p>
            </div>
        <form id="salePaymentForm" class="sales-payment-form" action="{{ $unpaidSales->isNotEmpty() ? route('owner.sales.payments.store', $unpaidSales->first()) : route('owner.sales') }}" method="POST">
            @csrf
            <div class="sales-payment-grid">
                <div class="owner-form-field"><label for="paymentDate">Tanggal penerimaan</label><input id="paymentDate" class="owner-form-control" name="paid_at" type="date" value="{{ now()->format('Y-m-d') }}" required></div>
                <div class="owner-form-field"><label for="paymentAccount">Diterima ke akun</label><select id="paymentAccount" class="owner-form-control" name="cash_account_id" required>@foreach($accounts as $account)<option value="{{ $account->id }}">{{ $account->name }}</option>@endforeach</select></div>
                <div class="owner-form-field"><label for="paymentShipment">Penjualan yang dilunasi</label><select id="paymentShipment" class="owner-form-control" required onchange="document.getElementById('salePaymentForm').action=this.value">
                    @forelse($unpaidSales as $unpaidSale)
                        <option value="{{ route('owner.sales.payments.store', $unpaidSale) }}">{{ $unpaidSale->no_nota }} · {{ $unpaidSale->pks_tujuan }} · Rp {{ number_format($unpaidSale->total_pendapatan, 0, ',', '.') }}</option>
                    @empty
                        <option value="" disabled selected>Tidak ada penjualan belum lunas</option>
                    @endforelse
                </select></div>
            </div>
            <div class="sales-form-actions"><button id="salePaymentSubmit" type="submit" class="owner-action-button" @disabled($unpaidSales->isEmpty())>Catat Pelunasan</button></div>
        </form>
        </section>
    </div>
    @endif

    @if($page === 'expenses')
    <section class="card" id="pengeluaran" style="margin-top:22px;">
        <div class="card-header"><h2>Pengeluaran / Operasional</h2></div>
        <form action="{{ route('owner.expenses.store') }}" method="POST" style="max-width:760px;">
            @csrf
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:12px;">
                <div><label for="expenseDate">Tanggal</label><input id="expenseDate" name="tanggal" type="date" value="{{ now()->format('Y-m-d') }}" required style="display:block;width:100%;padding:10px;"></div>
                <div><label for="expenseAccount">Akun pembayaran</label><select id="expenseAccount" name="cash_account_id" required style="display:block;width:100%;padding:10px;">@foreach($accounts as $account)<option value="{{ $account->id }}">{{ $account->name }}</option>@endforeach</select></div>
                <div><label for="expenseCategory">Kategori</label><select id="expenseCategory" name="kategori" required style="display:block;width:100%;padding:10px;"><option value="">Pilih kategori</option>@foreach($expenseCategories as $category => $classification)<option value="{{ $category }}">{{ $category }}</option>@endforeach</select></div>
                <div><label for="expenseAmount">Nominal (Rp)</label><input id="expenseAmount" name="nominal" type="text" inputmode="numeric" data-money-integer data-money-min="1" data-money-max="999999999999" placeholder="Contoh: 1.000.000" required style="display:block;width:100%;padding:10px;"></div>
                <div style="grid-column:1/-1;"><label for="expenseNote">Keterangan</label><textarea id="expenseNote" name="keterangan" maxlength="5000" rows="2" required style="display:block;width:100%;padding:10px;"></textarea></div>
            </div>
            <button type="submit" class="owner-action-button" style="margin-top:14px;">Simpan Pengeluaran</button>
        </form>
    </section>
    @endif

    @if($page === 'reports')
    <div class="reports-page" id="laporan">
        <section class="reports-section" aria-labelledby="reportPeriodTitle">
            <div class="reports-section-heading">
                <h2 id="reportPeriodTitle">Periode Laporan</h2>
                <p>Pilih rentang tanggal untuk membuat laporan laba rugi dan rincian transaksi.</p>
            </div>
            <form method="GET" action="{{ route('owner.reports') }}" class="report-period-form">
                <div class="owner-form-field">
                    <label for="reportFrom">Dari tanggal</label>
                    <input id="reportFrom" class="owner-form-control" type="date" name="from" value="{{ $period['from'] }}" required>
                </div>
                <div class="owner-form-field">
                    <label for="reportTo">Sampai tanggal</label>
                    <input id="reportTo" class="owner-form-control" type="date" name="to" value="{{ $period['to'] }}" required>
                </div>
                <button type="submit" class="owner-action-button">
                    <i class="fa-solid fa-filter"></i> Tampilkan Laporan
                </button>
                <div class="report-export-actions">
                    <a class="owner-action-button report-pdf-button"
                        href="{{ route('owner.reports.export', ['format' => 'pdf', 'from' => $period['from'], 'to' => $period['to']]) }}">
                        <i class="fa-solid fa-file-pdf"></i> Unduh PDF
                    </a>
                    <a class="owner-action-button report-sheet-button"
                        href="{{ route('owner.reports.export', ['format' => 'csv', 'from' => $period['from'], 'to' => $period['to']]) }}">
                        <i class="fa-solid fa-file-excel"></i> Spreadsheet
                    </a>
                </div>
            </form>
        </section>

        <section class="reports-section" aria-labelledby="incomeReportTitle">
            <div class="reports-section-heading">
                <h2 id="incomeReportTitle">Laba Rugi</h2>
                <p>Ringkasan pendapatan dan biaya periode {{ \Carbon\Carbon::parse($period['from'])->format('d/m/Y') }}–{{ \Carbon\Carbon::parse($period['to'])->format('d/m/Y') }}.</p>
            </div>
            <div class="reports-table-wrap">
                <table class="custom-table reports-table">
                    <tbody>
                        <tr><td>Pendapatan penjualan PKS</td><td>Rp {{ number_format($periodSummary['revenue'], 0, ',', '.') }}</td></tr>
                        <tr><td>HPP &amp; beban biaya langsung</td><td>(Rp {{ number_format($periodSummary['direct_expenses'], 0, ',', '.') }})</td></tr>
                        <tr><td>Biaya operasional</td><td>(Rp {{ number_format($periodSummary['operational_expenses'], 0, ',', '.') }})</td></tr>
                        <tr class="reports-profit-row"><td>Laba / (Rugi) bersih</td><td>Rp {{ number_format($periodSummary['net_profit'], 0, ',', '.') }}</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="reports-section" aria-labelledby="balanceReportTitle">
            <div class="reports-section-heading">
                <h2 id="balanceReportTitle">Neraca per {{ \Carbon\Carbon::parse($balanceSheet['date'])->format('d/m/Y') }}</h2>
                <p>Posisi aset, kewajiban, dan modal pada akhir periode yang dipilih.</p>
            </div>
            <div class="reports-table-wrap">
                <table class="custom-table reports-table">
                    <tbody>
                        <tr><td>Kas dan bank</td><td>Rp {{ number_format($balanceSheet['cash'], 0, ',', '.') }}</td></tr>
                        <tr><td>Persediaan (estimasi HPP)</td><td>Rp {{ number_format($balanceSheet['inventory'], 0, ',', '.') }}</td></tr>
                        <tr><td>Piutang</td><td>Rp {{ number_format($balanceSheet['receivables'], 0, ',', '.') }}</td></tr>
                        <tr class="reports-total-row"><td>Total aset</td><td>Rp {{ number_format($balanceSheet['total_assets'], 0, ',', '.') }}</td></tr>
                        <tr><td>Kewajiban</td><td>Rp {{ number_format($balanceSheet['liabilities'], 0, ',', '.') }}</td></tr>
                        <tr class="reports-profit-row"><td>Modal / saldo bersih</td><td>Rp {{ number_format($balanceSheet['equity'], 0, ',', '.') }}</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="reports-note">Persediaan dihitung berdasarkan estimasi harga beli rata-rata. Data kewajiban belum tersedia di aplikasi; modal merupakan selisih aset dan kewajiban yang tercatat.</p>
        </section>

        <section class="reports-section" aria-labelledby="transactionDetailTitle">
            <div class="reports-section-heading">
                <h2 id="transactionDetailTitle">Rincian Transaksi</h2>
                <p>{{ number_format($financialReport['summary']['transactions'], 0, ',', '.') }} transaksi tercatat pada periode yang dipilih.</p>
            </div>
            <div class="reports-table-wrap">
                <table class="custom-table reports-table reports-detail-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th><th>No. Referensi</th><th>Jenis</th><th>Kategori / Pihak</th>
                            <th>Keterangan</th><th>Jumlah (Rp)</th><th>Arus</th><th>Status / Akun</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($financialReport['rows'] as $row)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                                <td>{{ $row['number'] }}</td>
                                <td>{{ $row['type'] }}</td>
                                <td>{{ $row['category'] }}</td>
                                <td>{{ $row['description'] }}</td>
                                <td class="report-amount">Rp {{ number_format($row['amount'], 0, ',', '.') }}</td>
                                <td>{{ $row['flow'] }}</td>
                                <td>{{ $row['status'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8">Tidak ada transaksi pada periode ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
    @endif
@endsection
