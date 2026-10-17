<?php

namespace Tests\Feature;

use App\Models\CashAccount;
use App\Models\CashInflow;
use App\Models\CashOutflow;
use App\Models\Purchase;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_menu_routes_render_only_the_selected_page(): void
    {
        $owner = $this->createOwner();

        $this->actingAs($owner)
            ->get(route('owner.dashboard'))
            ->assertOk()
            ->assertSee('Total Kas &amp; Bank', false)
            ->assertDontSee('Tambah Rekening Bank')
            ->assertDontSee('Rincian Penjualan Hari Ini')
            ->assertDontSee('Form Input Penjualan')
            ->assertDontSee('Laporan Neraca');

        $this->actingAs($owner)
            ->get(route('owner.cash'))
            ->assertOk()
            ->assertSee('Tambah Rekening Bank')
            ->assertSee('Transfer ke Kas RAM Admin')
            ->assertDontSee('Rincian Penjualan Hari Ini')
            ->assertDontSee('Form Input Penjualan')
            ->assertDontSee('Laporan Neraca');

        $this->actingAs($owner)
            ->get(route('owner.sales'))
            ->assertOk()
            ->assertSee('Rincian Penjualan Hari Ini')
            ->assertSee('Form Input Penjualan')
            ->assertDontSee('Tambah Rekening Bank')
            ->assertDontSee('Form Pengeluaran Operasional')
            ->assertDontSee('Laporan Neraca');

        $this->actingAs($owner)
            ->get(route('owner.expenses'))
            ->assertOk()
            ->assertSee('Pengeluaran / Operasional')
            ->assertSee('Simpan Pengeluaran')
            ->assertDontSee('Tambah Rekening Bank')
            ->assertDontSee('Form Input Penjualan')
            ->assertDontSee('Laporan Neraca');

        $this->actingAs($owner)
            ->get(route('owner.reports'))
            ->assertOk()
            ->assertSee('Laba Rugi')
            ->assertSee('Neraca per')
            ->assertDontSee('Tambah Rekening Bank')
            ->assertDontSee('Form Input Penjualan');
    }

    public function test_owner_dashboard_uses_saved_financial_data_for_sales_and_account_balances(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 14)->setTime(12, 0));
        $owner = $this->createOwner();
        $cash = CashAccount::where('owner_id', $owner->id)->where('type', 'cash')->firstOrFail();
        $bank = CashAccount::create([
            'owner_id' => $owner->id,
            'name' => 'BCA Operasional',
            'type' => 'bank',
            'initial_balance' => 1000000,
        ]);

        CashInflow::create([
            'no_transaksi' => 'KM-261014-001',
            'cash_account_id' => $cash->id,
            'tanggal' => '2026-10-14',
            'sumber' => 'Modal',
            'nominal' => 250000,
            'keterangan' => 'Modal usaha',
        ]);
        Purchase::create([
            'no_nota' => 'BL-261014-001',
            'tanggal' => '2026-10-14',
            'petani' => 'Petani Owner',
            'kategori' => 'TBS',
            'harga_per_kg' => 2500,
            'bruto' => 1100,
            'tara' => 100,
            'netto_1' => 1000,
            'rafaksi' => 0,
            'netto_2' => 1000,
            'upah_bongkar' => 0,
            'total_pembelian' => 90000,
            'total_bayar' => 100000,
            'cash_account_id' => $cash->id,
        ]);
        Shipment::create([
            'no_nota' => 'SJ-261014-001',
            'tanggal' => '2026-10-14',
            'pks_tujuan' => 'PKS Owner',
            'nopol' => 'BG 1234 AB',
            'driver' => 'Supir Owner',
            'tbs_kg' => 100,
            'brondolan_kg' => 0,
            'brd_afkir_kg' => 0,
            'buah_kecil_kg' => 0,
            'total_kg' => 100,
            'harga_jual_per_kg' => 4000,
            'total_pendapatan' => 400000,
            'is_paid' => true,
            'paid_at' => '2026-10-14',
            'cash_account_id' => $bank->id,
        ]);
        CashOutflow::create([
            'no_transaksi' => 'KK-261014-001',
            'tanggal' => '2026-10-14',
            'kategori' => 'Listrik',
            'nominal' => 50000,
            'keterangan' => 'Tagihan listrik',
            'cash_account_id' => $bank->id,
        ]);

        $this->actingAs($owner)
            ->get(route('owner.dashboard', ['from' => '2026-10-14', 'to' => '2026-10-14']))
            ->assertOk()
            ->assertSee('Rp 1.500.000')
            ->assertSee('Ringkasan');

        $this->actingAs($owner)
            ->get(route('owner.sales', ['from' => '2026-10-14', 'to' => '2026-10-14']))
            ->assertOk()
            ->assertSee('SJ-261014-001')
            ->assertSee('Rp 400.000')
            ->assertSee('Rincian Penjualan Hari Ini');

        $this->actingAs($owner)
            ->get(route('owner.reports', ['from' => '2026-10-14', 'to' => '2026-10-14']))
            ->assertOk()
            ->assertSee('Neraca per')
            ->assertSee('Rp 260.000');
    }

    public function test_owner_can_export_period_financial_report_to_pdf_and_spreadsheet(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 14)->setTime(12, 0));
        $owner = $this->createOwner();
        $cash = CashAccount::where('owner_id', $owner->id)->where('type', 'cash')->firstOrFail();
        CashInflow::create([
            'no_transaksi' => 'KM-261014-001',
            'cash_account_id' => $cash->id,
            'tanggal' => '2026-10-14',
            'sumber' => 'Modal usaha',
            'nominal' => 250000,
            'keterangan' => 'Setoran modal',
        ]);
        Purchase::create([
            'no_nota' => 'BL-261014-001',
            'tanggal' => '2026-10-14',
            'petani' => 'Petani Laporan',
            'kategori' => 'TBS',
            'harga_per_kg' => 2500,
            'bruto' => 110,
            'tara' => 10,
            'netto_1' => 100,
            'rafaksi' => 0,
            'netto_2' => 100,
            'upah_bongkar' => 0,
            'total_pembelian' => 250000,
            'total_bayar' => 250000,
            'cash_account_id' => $cash->id,
        ]);
        Shipment::create([
            'no_nota' => 'SJ-261014-001',
            'tanggal' => '2026-10-14',
            'pks_tujuan' => 'PKS Laporan',
            'nopol' => 'BG 1234 AB',
            'driver' => 'Supir Laporan',
            'tbs_kg' => 100,
            'brondolan_kg' => 0,
            'brd_afkir_kg' => 0,
            'buah_kecil_kg' => 0,
            'total_kg' => 100,
            'harga_jual_per_kg' => 4000,
            'total_pendapatan' => 400000,
        ]);

        $period = ['from' => '2026-10-14', 'to' => '2026-10-14'];
        $pdfResponse = $this->actingAs($owner)
            ->get(route('owner.reports.export', array_merge(['format' => 'pdf'], $period)))
            ->assertOk()
            ->assertDownload('laporan-keuangan-2026-10-14-2026-10-14.pdf')
            ->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $pdfResponse->getContent());

        $csvResponse = $this->actingAs($owner)
            ->get(route('owner.reports.export', array_merge(['format' => 'csv'], $period)))
            ->assertOk()
            ->assertDownload('laporan-keuangan-2026-10-14-2026-10-14.csv')
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csvContent = $csvResponse->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csvContent);
        $this->assertStringContainsString('SJ-261014-001', $csvContent);
        $this->assertStringContainsString('BL-261014-001', $csvContent);
        $this->assertStringContainsString('NERACA PER 2026-10-14', $csvContent);
    }

    public function test_owner_can_create_bank_account_transfer_and_operational_expense(): void
    {
        $owner = $this->createOwner();
        $cash = CashAccount::where('owner_id', $owner->id)->where('type', 'cash')->firstOrFail();
        $cash->update(['initial_balance' => 2000000]);

        $this->actingAs($owner)
            ->post(route('owner.accounts.store'), [
                'name' => 'BCA Owner',
                'initial_balance' => 1000000,
            ])
            ->assertRedirect(route('owner.cash'))
            ->assertSessionHas('success');

        $bank = CashAccount::where('owner_id', $owner->id)->where('name', 'BCA Owner')->firstOrFail();
        $adminRam = CashAccount::whereNull('owner_id')->where('name', 'KAS RAM')->firstOrFail();
        $adminRam->update(['type' => 'bank']);
        $this->assertSame(0.0, array_sum(app(\App\Services\CashAccountBalanceService::class)->balances()));
        $this->assertSame($owner->id, $bank->owner_id);

        $this->actingAs($owner)
            ->post(route('owner.transfers.store'), [
                'tanggal' => '2026-10-14',
                'destination_account_id' => $adminRam->id,
                'nominal' => 1000000,
                'keterangan' => 'Pindah dana',
            ])
            ->assertRedirect(route('owner.cash'))
            ->assertSessionHas('success');

        $this->actingAs($owner)
            ->post(route('owner.expenses.store'), [
                'tanggal' => '2026-10-14',
                'cash_account_id' => $bank->id,
                'kategori' => 'Listrik',
                'nominal' => 50000,
                'keterangan' => 'Tagihan bulanan',
            ])
            ->assertRedirect(route('owner.expenses'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('cash_transfers', [
            'source_account_id' => $cash->id,
            'destination_account_id' => $adminRam->id,
            'nominal' => 1000000,
        ]);
        $this->assertDatabaseHas('cash_outflows', [
            'cash_account_id' => $bank->id,
            'kategori' => 'Listrik',
            'nominal' => 50000,
        ]);

        $this->actingAs($owner)
            ->get(route('owner.cash'))
            ->assertOk()
            ->assertSee('BCA Owner')
            ->assertSee('Rp 950.000')
            ->assertSee('KAS RAM')
            ->assertSee('TF-');
    }

    public function test_owner_sales_create_the_same_shipment_record_and_reduce_stock(): void
    {
        $owner = $this->createOwner();
        Purchase::create([
            'no_nota' => 'BL-261014-001',
            'tanggal' => '2026-10-14',
            'petani' => 'Petani Stok',
            'kategori' => 'TBS',
            'harga_per_kg' => 2500,
            'bruto' => 1100,
            'tara' => 100,
            'netto_1' => 1000,
            'rafaksi' => 0,
            'netto_2' => 1000,
            'upah_bongkar' => 0,
            'total_pembelian' => 2500000,
            'total_bayar' => 2500000,
        ]);

        $this->actingAs($owner)
            ->post(route('owner.sales.store'), [
                'tanggal' => '2026-10-14',
                'pks_tujuan' => 'PKS Baru',
                'nopol' => 'BG 4321 CD',
                'driver' => 'Driver Owner',
                'harga_jual_per_kg' => 4000,
                'tbs_kg' => 500,
                'brondolan_kg' => 0,
                'brd_afkir_kg' => 0,
                'buah_kecil_kg' => 0,
                'catatan' => 'Penjualan owner',
            ])
            ->assertRedirect(route('owner.sales', ['from' => '2026-10-01', 'to' => '2026-10-14']))
            ->assertSessionHas('success');

        $sale = Shipment::firstOrFail();
        $this->assertSame('500.00', $sale->total_kg);
        $this->assertSame('2000000.00', $sale->total_pendapatan);
        $this->assertFalse($sale->is_paid);
        $this->assertNull($sale->cash_account_id);
        $this->assertSame(500.0, Shipment::availableStock()['TBS']);
    }

    public function test_owner_can_record_full_sale_payment_once_to_a_selected_account(): void
    {
        $owner = $this->createOwner();
        $bank = CashAccount::create([
            'owner_id' => $owner->id,
            'name' => 'Mandiri Pembayaran',
            'type' => 'bank',
            'initial_balance' => 0,
        ]);
        $shipment = Shipment::create([
            'no_nota' => 'SJ-261014-001',
            'tanggal' => '2026-10-14',
            'pks_tujuan' => 'PKS Pelunasan',
            'nopol' => 'BG 4321 CD',
            'driver' => 'Driver Owner',
            'tbs_kg' => 500,
            'brondolan_kg' => 0,
            'brd_afkir_kg' => 0,
            'buah_kecil_kg' => 0,
            'total_kg' => 500,
            'harga_jual_per_kg' => 4000,
            'total_pendapatan' => 2000000,
        ]);

        $this->actingAs($owner)
            ->post(route('owner.sales.payments.store', $shipment), [
                'paid_at' => '2026-10-15',
                'cash_account_id' => $bank->id,
            ])
            ->assertRedirect(route('owner.sales'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('shipments', [
            'id' => $shipment->id,
            'is_paid' => 1,
            'cash_account_id' => $bank->id,
        ]);
        $this->assertSame('2026-10-15', $shipment->fresh()->paid_at->format('Y-m-d'));

        $balance = app(\App\Services\CashAccountBalanceService::class)->balances(null, $owner->id);
        $this->assertSame(2000000.0, $balance[$bank->id]);

        $this->actingAs($owner)
            ->from(route('owner.sales'))
            ->post(route('owner.sales.payments.store', $shipment), [
                'paid_at' => '2026-10-16',
                'cash_account_id' => $bank->id,
            ])
            ->assertRedirect(route('owner.sales'))
            ->assertSessionHasErrors('shipment');

        $this->assertSame(2000000.0, app(\App\Services\CashAccountBalanceService::class)->balances(null, $owner->id)[$bank->id]);
    }

    public function test_owner_transfer_cannot_target_an_owner_account_instead_of_admin_kas_ram(): void
    {
        $owner = $this->createOwner();
        $bank = CashAccount::create([
            'owner_id' => $owner->id,
            'name' => 'BCA Owner',
            'type' => 'bank',
            'initial_balance' => 0,
        ]);

        $this->actingAs($owner)
            ->from(route('owner.cash'))
            ->post(route('owner.transfers.store'), [
                'tanggal' => '2026-10-14',
                'destination_account_id' => $bank->id,
                'nominal' => 1000,
            ])
            ->assertRedirect(route('owner.cash'))
            ->assertSessionHasErrors('destination_account_id');

        $this->assertDatabaseCount('cash_transfers', 0);
    }

    public function test_owner_topups_are_private_until_owner_transfers_to_admin_kas_ram(): void
    {
        $owner = $this->createOwner();
        $ownerCash = CashAccount::where('owner_id', $owner->id)->where('type', 'cash')->firstOrFail();
        $ownerBank = CashAccount::create([
            'owner_id' => $owner->id,
            'name' => 'BCA Owner',
            'type' => 'bank',
            'initial_balance' => 1000000,
        ]);
        $adminRam = CashAccount::whereNull('owner_id')->where('name', 'KAS RAM')->firstOrFail();
        $balanceService = app(\App\Services\CashAccountBalanceService::class);

        $this->actingAs($owner)
            ->post(route('owner.cash.balance.store'), [
                'tanggal' => '2026-10-14',
                'nominal' => 3000000,
                'keterangan' => 'Modal tunai Owner',
            ])
            ->assertRedirect(route('owner.cash'))
            ->assertSessionHas('success');

        $this->assertSame(0.0, array_sum($balanceService->balances()));
        $this->assertSame(4000000.0, array_sum($balanceService->balances(null, $owner->id)));

        $this->actingAs($owner)
            ->post(route('owner.transfers.store'), [
                'tanggal' => '2026-10-14',
                'destination_account_id' => $adminRam->id,
                'nominal' => 2000000,
                'keterangan' => 'Setoran kas operasional',
            ])
            ->assertRedirect(route('owner.cash'))
            ->assertSessionHas('success');

        $this->assertSame(2000000.0, array_sum($balanceService->balances(null, $owner->id)));
        $this->assertSame(2000000.0, array_sum($balanceService->balances()));
        $this->assertSame(2000000.0, $balanceService->balances()[$adminRam->id]);
        $this->assertSame(1000000.0, $balanceService->balances(null, $owner->id)[$ownerBank->id]);
    }

    public function test_owner_dashboard_restores_missing_admin_kas_ram_account(): void
    {
        $owner = $this->createOwner();
        CashAccount::whereNull('owner_id')->where('name', 'KAS RAM')->delete();

        $this->actingAs($owner)
            ->get(route('owner.dashboard'))
            ->assertOk();

        $this->assertDatabaseHas('cash_accounts', [
            'owner_id' => null,
            'name' => 'KAS RAM',
            'type' => 'cash',
            'initial_balance' => 0,
        ]);
    }

    private function createOwner(): User
    {
        $owner = User::create([
            'name' => 'Owner Dashboard',
            'username' => 'owner-dashboard',
            'role' => 'owner',
            'password' => bcrypt('password'),
        ]);

        CashAccount::create([
            'owner_id' => $owner->id,
            'name' => 'Kas Utama',
            'type' => 'cash',
            'initial_balance' => 0,
        ]);

        return $owner;
    }
}
