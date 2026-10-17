<?php

namespace Tests\Feature;

use App\Models\CashInflow;
use App\Models\CashOutflow;
use App\Models\Purchase;
use App\Models\Receivable;
use App\Models\ReceivableTransaction;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_center_prompts_for_a_date_range_before_filtering(): void
    {
        $this->actingAs($this->createAdmin())
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('Pusat Laporan')
            ->assertSee('Silakan filter laporan terlebih dahulu.')
            ->assertSee('Semua Laporan')
            ->assertSee('Laporan Pembelian')
            ->assertSee('Laporan Pengiriman PKS')
            ->assertSee('Laporan Arus Kas')
            ->assertSee('Laporan Laba Rugi');
    }

    public function test_all_reports_can_be_generated_and_downloaded_as_pdf(): void
    {
        $admin = $this->createAdmin();
        $this->createPurchase('BL-261014-ALL', '2026-10-14', 'Petani Semua Laporan', 500000);
        $this->createShipment('SJ-261014-ALL', '2026-10-14', 125.5);
        CashInflow::create([
            'no_transaksi' => 'KM-261014-ALL',
            'tanggal' => '2026-10-14',
            'sumber' => 'Setoran',
            'nominal' => 250000,
            'keterangan' => 'Modal usaha',
        ]);
        CashOutflow::create([
            'no_transaksi' => 'KK-261014-ALL',
            'tanggal' => '2026-10-14',
            'kategori' => 'Listrik',
            'nominal' => 50000,
            'keterangan' => 'Tagihan listrik',
        ]);
        $period = [
            'from' => '2026-10-14',
            'to' => '2026-10-14',
            'type' => 'all',
        ];

        $this->actingAs($admin)
            ->get(route('admin.reports.index', $period))
            ->assertOk()
            ->assertSee('Laporan Pembelian')
            ->assertSee('Laporan Pengiriman PKS')
            ->assertSee('Laporan Arus Kas')
            ->assertSee('Laporan Laba Rugi')
            ->assertSee('BL-261014-ALL')
            ->assertSee('SJ-261014-ALL')
            ->assertSee('KM-261014-ALL')
            ->assertSee('Format unduhan: PDF')
            ->assertDontSee('Excel')
            ->assertSee('Unduh Laporan');

        $pdfResponse = $this->actingAs($admin)
            ->get(route('admin.reports.export', $period))
            ->assertOk()
            ->assertDownload('laporan-semua-laporan-2026-10-14-2026-10-14.pdf')
            ->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $pdfResponse->getContent());
        $this->assertSame(2, preg_match_all('/\/Type\s*\/Page\b/', $pdfResponse->getContent()));
    }

    public function test_purchase_and_shipment_reports_are_filtered_by_date_range(): void
    {
        $admin = $this->createAdmin();
        $this->createPurchase('BL-261014-001', '2026-10-14', 'Petani Dalam', 500000);
        $this->createPurchase('BL-261016-001', '2026-10-16', 'Petani Luar', 700000);
        $this->createShipment('SJ-261014-001', '2026-10-14', 125.5);

        $this->actingAs($admin)
            ->get(route('admin.reports.index', [
                'from' => '2026-10-14',
                'to' => '2026-10-15',
                'type' => 'purchases',
            ]))
            ->assertOk()
            ->assertSee('Petani Dalam')
            ->assertDontSee('Petani Luar')
            ->assertSee('Rp 500.000')
            ->assertSee('Total dibayarkan');

        $this->actingAs($admin)
            ->get(route('admin.reports.index', [
                'from' => '2026-10-14',
                'to' => '2026-10-15',
                'type' => 'shipments',
            ]))
            ->assertOk()
            ->assertSee('SJ-261014-001')
            ->assertSee('SLUM')
            ->assertSee('125,50');
    }

    public function test_cash_flow_report_summarizes_inflows_and_outflows(): void
    {
        $admin = $this->createAdmin();
        CashInflow::create([
            'no_transaksi' => 'KM-261014-001',
            'tanggal' => '2026-10-14',
            'sumber' => 'PUSAT',
            'nominal' => 20000000,
            'keterangan' => 'Terima Modal OP',
        ]);
        CashOutflow::create([
            'no_transaksi' => 'KK-261014-001',
            'tanggal' => '2026-10-14',
            'kategori' => 'Upah Muat',
            'nominal' => 376500,
            'keterangan' => 'TRIP 2',
        ]);
        $this->assertDatabaseCount('cash_inflows', 1);
        $this->assertDatabaseCount('cash_outflows', 1);

        $this->actingAs($admin)
            ->get(route('admin.reports.index', [
                'from' => '2026-10-14',
                'to' => '2026-10-14',
                'type' => 'cash-flow',
            ]))
            ->assertOk()
            ->assertSee('Total kas masuk')
            ->assertSee('Rp 20.000.000')
            ->assertSee('Total kas keluar')
            ->assertSee('Rp 376.500')
            ->assertSee('Saldo bersih')
            ->assertSee('Rp 19.623.500')
            ->assertSee('KM-261014-001')
            ->assertSee('KK-261014-001');
    }

    public function test_cash_flow_report_includes_purchase_payments_and_receivable_installments(): void
    {
        $admin = $this->createAdmin();
        $this->createPurchase('BL-261014-001', '2026-10-14', 'Petani Arus Kas', 2000000);
        $receivable = Receivable::create([
            'no_transaksi' => 'PTG-261014-001',
            'tanggal' => '2026-10-14',
            'pelanggan' => 'Pelanggan Arus Kas',
            'pemberi_pinjaman' => $admin->name,
            'nominal_awal' => 3000000,
        ]);
        $receivable->transactions()->create([
            'no_pembayaran' => 'PTG-261014-001-C001',
            'tanggal' => '2026-10-14',
            'jenis' => ReceivableTransaction::TYPE_PAYMENT,
            'nominal' => 1000000,
            'metode' => 'Tunai',
            'admin_penerima' => $admin->name,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.reports.index', [
                'from' => '2026-10-14',
                'to' => '2026-10-14',
                'type' => 'cash-flow',
            ]))
            ->assertOk()
            ->assertSee('Pembayaran pembelian Petani Arus Kas')
            ->assertSee('Pembayaran PTG-261014-001 via Tunai')
            ->assertSee('Total kas masuk')
            ->assertSee('Rp 1.000.000')
            ->assertSee('Total kas keluar')
            ->assertSee('Rp 2.000.000')
            ->assertSee('Saldo bersih')
            ->assertSee('Rp -1.000.000');
    }

    public function test_cash_flow_records_shipment_revenue_on_the_actual_payment_date(): void
    {
        $admin = $this->createAdmin();
        $shipment = $this->createShipment('SJ-261014-001', '2026-10-14', 100);
        $shipment->update([
            'harga_jual_per_kg' => 4000,
            'total_pendapatan' => 400000,
            'is_paid' => true,
            'paid_at' => '2026-10-16',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.reports.index', [
                'from' => '2026-10-14',
                'to' => '2026-10-14',
                'type' => 'cash-flow',
            ]))
            ->assertOk()
            ->assertDontSee('SJ-261014-001')
            ->assertSee('Saldo bersih')
            ->assertSee('Rp 0');

        $this->actingAs($admin)
            ->get(route('admin.reports.index', [
                'from' => '2026-10-16',
                'to' => '2026-10-16',
                'type' => 'cash-flow',
            ]))
            ->assertOk()
            ->assertSee('SJ-261014-001')
            ->assertSee('Penjualan PKS - SLUM')
            ->assertSee('Rp 400.000');
    }

    public function test_profit_loss_report_classifies_purchase_cost_direct_expenses_and_operating_expenses(): void
    {
        $admin = $this->createAdmin();
        $purchase = $this->createPurchase('BL-261014-001', '2026-10-14', 'Petani Laba', 120000);
        $purchase->update(['total_pembelian' => 130000]);
        $shipment = $this->createShipment('SJ-261014-001', '2026-10-14', 100);
        $shipment->update([
            'harga_jual_per_kg' => 4000,
            'total_pendapatan' => 400000,
        ]);
        foreach ([
            ['Beban Fee Pembelian barang', 1000],
            ['Upah Bongkar Barang', 2000],
            ['Upah Muat Barang', 3000],
            ['Ongkos Armada', 4000],
            ['Listrik', 10000],
            ['Kuota Internet', 5000],
            ['OP', 10000],
        ] as $index => [$category, $amount]) {
            CashOutflow::create([
                'no_transaksi' => 'KK-261014-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'tanggal' => '2026-10-14',
                'kategori' => $category,
                'nominal' => $amount,
                'keterangan' => 'Biaya '.$category,
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.reports.index', [
                'from' => '2026-10-14',
                'to' => '2026-10-14',
                'type' => 'profit-loss',
            ]))
            ->assertOk()
            ->assertSee('Pendapatan penjualan PKS:')
            ->assertSee('Rp 400.000')
            ->assertSee('HPP pembelian sawit:')
            ->assertSee('Rp 130.000')
            ->assertSee('Beban langsung kategori 1–4:')
            ->assertSee('Rp 10.000')
            ->assertSee('Biaya operasional kategori 5:')
            ->assertSee('Rp 25.000')
            ->assertSee('Beban Fee Pembelian barang')
            ->assertSee('Upah Bongkar Barang')
            ->assertSee('Upah Muat Barang')
            ->assertSee('Ongkos Armada')
            ->assertSee('Kuota Internet')
            ->assertSee('Biaya Operasional')
            ->assertSee('Laba bersih')
            ->assertSee('Rp 235.000');
    }

    public function test_report_requires_valid_complete_date_range_and_type(): void
    {
        $this->actingAs($this->createAdmin())
            ->from(route('admin.reports.index'))
            ->get(route('admin.reports.index', [
                'from' => '2026-10-16',
                'to' => '2026-10-14',
                'type' => 'purchases',
            ]))
            ->assertRedirect(route('admin.reports.index'))
            ->assertSessionHasErrors('to');
    }

    private function createAdmin(): User
    {
        return User::create([
            'name' => 'Admin Laporan',
            'username' => 'admin-laporan',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);
    }

    private function createPurchase(string $number, string $date, string $farmer, float $paid): Purchase
    {
        return Purchase::create([
            'no_nota' => $number,
            'tanggal' => $date,
            'petani' => $farmer,
            'kategori' => 'TBS',
            'harga_per_kg' => 2500,
            'bruto' => 250,
            'tara' => 50,
            'netto_1' => 200,
            'rafaksi' => 0,
            'netto_2' => 200,
            'upah_bongkar' => 0,
            'total_pembelian' => $paid,
            'total_bayar' => $paid,
        ]);
    }

    private function createShipment(string $number, string $date, float $total): Shipment
    {
        return Shipment::create([
            'no_nota' => $number,
            'tanggal' => $date,
            'pks_tujuan' => 'SLUM',
            'nopol' => 'BG 1234 AB',
            'driver' => 'Angga',
            'tbs_kg' => $total,
            'brondolan_kg' => 0,
            'brd_afkir_kg' => 0,
            'buah_kecil_kg' => 0,
            'total_kg' => $total,
        ]);
    }
}
