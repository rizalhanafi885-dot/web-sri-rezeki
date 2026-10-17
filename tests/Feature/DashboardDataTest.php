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

class DashboardDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_kpis_stock_valuation_and_chart_use_saved_transactions(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 14)->setTime(12, 0));
        $admin = User::create([
            'name' => 'Admin Dashboard',
            'username' => 'admin-dashboard-data',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        CashInflow::create([
            'no_transaksi' => 'KM-261014-001',
            'tanggal' => '2026-10-14',
            'sumber' => 'PUSAT',
            'nominal' => 5000000,
            'keterangan' => 'Modal',
        ]);
        CashOutflow::create([
            'no_transaksi' => 'KK-261014-001',
            'tanggal' => '2026-10-14',
            'kategori' => 'Operasional',
            'nominal' => 500000,
            'keterangan' => 'Biaya operasional',
        ]);
        Purchase::create([
            'no_nota' => 'BL-261014-001',
            'tanggal' => '2026-10-14',
            'petani' => 'Petani Dashboard',
            'kategori' => 'TBS',
            'harga_per_kg' => 2000,
            'bruto' => 1100,
            'tara' => 100,
            'netto_1' => 1000,
            'rafaksi' => 5,
            'netto_2' => 950,
            'upah_bongkar' => 0,
            'total_pembelian' => 1900000,
            'total_bayar' => 2000000,
        ]);
        Shipment::create([
            'no_nota' => 'SJ-261014-001',
            'tanggal' => '2026-10-14',
            'pks_tujuan' => 'SLUM',
            'nopol' => 'BG 1234 AB',
            'driver' => 'Angga',
            'tbs_kg' => 500,
            'brondolan_kg' => 0,
            'brd_afkir_kg' => 0,
            'buah_kecil_kg' => 0,
            'total_kg' => 500,
        ]);
        $receivable = Receivable::create([
            'no_transaksi' => 'PTG-261014-001',
            'tanggal' => '2026-10-14',
            'pelanggan' => 'Pelanggan Dashboard',
            'pemberi_pinjaman' => $admin->name,
            'nominal_awal' => 3000000,
        ]);
        $receivable->transactions()->createMany([
            [
                'no_pembayaran' => 'PTG-261014-001-T001',
                'tanggal' => '2026-10-14',
                'jenis' => ReceivableTransaction::TYPE_LOAN,
                'nominal' => 500000,
                'metode' => 'Pinjaman Tambahan',
                'admin_penerima' => $admin->name,
            ],
            [
                'no_pembayaran' => 'PTG-261014-001-C001',
                'tanggal' => '2026-10-14',
                'jenis' => ReceivableTransaction::TYPE_PAYMENT,
                'nominal' => 1000000,
                'metode' => 'Tunai',
                'admin_penerima' => $admin->name,
            ],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Rp 3.500.000')
            ->assertSee('1.000 Kg')
            ->assertSee('Rp 2.000.000')
            ->assertSee('500 Kg')
            ->assertSee('Rp 2.500.000')
            ->assertSee('Rp 950.000')
            ->assertSee('"purchases":[0,1,0,0]', false)
            ->assertSee('"shipments":[0,0.5,0,0]', false);
    }
}
