<?php

namespace Tests\Feature;

use App\Models\CashOutflow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashOutflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_and_save_cash_outflow(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->get(route('admin.cash-outflows.index'))
            ->assertOk()
            ->assertSee('Pencatatan Kas Keluar')
            ->assertSee('+ Catat Pengeluaran')
            ->assertSee('Beban Fee Pembelian barang')
            ->assertSee('Upah Bongkar Barang')
            ->assertSee('Upah Muat Barang')
            ->assertSee('Ongkos Armada')
            ->assertSee('Listrik')
            ->assertSee('Kuota Internet')
            ->assertSee('CCTV')
            ->assertSee('Gaji Karyawan')
            ->assertSee('Biaya Operasional Lainnya');

        $response = $this->actingAs($admin)->post(route('admin.cash-outflows.store'), [
            'tanggal' => '2026-10-14',
            'kategori' => 'Upah Muat Barang',
            'nominal' => 376500,
            'keterangan' => 'TRIP 2 7530 x 50',
        ]);

        $response->assertRedirect(route('admin.cash-outflows.index'));
        $outflow = CashOutflow::firstOrFail();
        $this->assertSame('KK-261014-001', $outflow->no_transaksi);
        $this->assertDatabaseHas('cash_outflows', [
            'no_transaksi' => 'KK-261014-001',
            'kategori' => 'Upah Muat Barang',
            'nominal' => 376500,
            'keterangan' => 'TRIP 2 7530 x 50',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.cash-outflows.index'))
            ->assertOk()
            ->assertSee('KK-261014-001')
            ->assertSee('Contoh: 1.000.000')
            ->assertSee('Upah Muat Barang')
            ->assertSee('TRIP 2 7530 x 50')
            ->assertSee('- Rp 376.500')
            ->assertSee('Edit')
            ->assertDontSee('Hapus');
    }

    public function test_admin_can_edit_cash_outflow_and_keep_it_in_the_same_category_classification(): void
    {
        $admin = $this->createAdmin();
        $outflow = CashOutflow::create([
            'no_transaksi' => 'KK-261014-001',
            'tanggal' => '2026-10-14',
            'kategori' => 'Upah Muat Barang',
            'nominal' => 376500,
            'keterangan' => 'TRIP 2',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.cash-outflows.update', $outflow), [
                'tanggal' => '2026-10-15',
                'kategori' => 'Listrik',
                'nominal' => 500000,
                'keterangan' => 'Koreksi tagihan listrik',
            ])
            ->assertRedirect(route('admin.cash-outflows.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('cash_outflows', [
            'id' => $outflow->id,
            'no_transaksi' => 'KK-261014-001',
            'kategori' => 'Listrik',
            'nominal' => 500000,
            'keterangan' => 'Koreksi tagihan listrik',
        ]);
        $this->assertSame('2026-10-15', $outflow->fresh()->tanggal->format('Y-m-d'));
        $this->assertDatabaseCount('cash_outflows', 1);
    }

    public function test_cash_outflow_edit_rejects_invalid_category_and_keeps_original_data(): void
    {
        $admin = $this->createAdmin();
        $outflow = CashOutflow::create([
            'no_transaksi' => 'KK-261014-001',
            'tanggal' => '2026-10-14',
            'kategori' => 'Upah Muat Barang',
            'nominal' => 376500,
            'keterangan' => 'TRIP 2',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.cash-outflows.index'))
            ->put(route('admin.cash-outflows.update', $outflow), [
                'tanggal' => '2026-10-15',
                'kategori' => 'Kategori tidak dikenal',
                'nominal' => 500000,
                'keterangan' => 'Koreksi',
                '_cash_outflow_id' => $outflow->id,
            ])
            ->assertRedirect(route('admin.cash-outflows.index'))
            ->assertSessionHasErrors('kategori');

        $this->assertSame('Upah Muat Barang', $outflow->fresh()->kategori);
        $this->assertSame('376500.00', $outflow->fresh()->nominal);
    }

    public function test_cash_outflow_can_edit_legacy_category_without_changing_it(): void
    {
        $admin = $this->createAdmin();
        $outflow = CashOutflow::create([
            'no_transaksi' => 'KK-261014-001',
            'tanggal' => '2026-10-14',
            'kategori' => 'OP',
            'nominal' => 376500,
            'keterangan' => 'Biaya lama',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.cash-outflows.update', $outflow), [
                'tanggal' => '2026-10-15',
                'kategori' => 'OP',
                'nominal' => 500000,
                'keterangan' => 'Koreksi biaya lama',
            ])
            ->assertRedirect(route('admin.cash-outflows.index'))
            ->assertSessionHas('success');

        $this->assertSame('OP', $outflow->fresh()->kategori);
        $this->assertSame('500000.00', $outflow->fresh()->nominal);
    }

    public function test_cash_outflow_rejects_non_positive_amount_and_missing_fields(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->from(route('admin.cash-outflows.index'))
            ->post(route('admin.cash-outflows.store'), [
                'tanggal' => '2026-10-14',
                'kategori' => '',
                'nominal' => 0,
                'keterangan' => '',
            ])
            ->assertRedirect(route('admin.cash-outflows.index'))
            ->assertSessionHasErrors(['kategori', 'nominal', 'keterangan']);

        $this->assertDatabaseCount('cash_outflows', 0);
    }

    public function test_cash_outflow_nominal_must_be_a_whole_rupiah_amount(): void
    {
        $this->actingAs($this->createAdmin())
            ->from(route('admin.cash-outflows.index'))
            ->post(route('admin.cash-outflows.store'), [
                'tanggal' => '2026-10-14',
                'kategori' => 'Listrik',
                'nominal' => '7777.77',
                'keterangan' => 'Biaya operasional',
            ])
            ->assertRedirect(route('admin.cash-outflows.index'))
            ->assertSessionHasErrors('nominal');

        $this->assertDatabaseCount('cash_outflows', 0);
    }

    public function test_cash_outflow_only_accepts_configured_expense_categories(): void
    {
        $this->actingAs($this->createAdmin())
            ->from(route('admin.cash-outflows.index'))
            ->post(route('admin.cash-outflows.store'), [
                'tanggal' => '2026-10-14',
                'kategori' => 'Kategori Lain',
                'nominal' => 100000,
                'keterangan' => 'Biaya test',
            ])
            ->assertRedirect(route('admin.cash-outflows.index'))
            ->assertSessionHasErrors('kategori');

        $this->assertDatabaseCount('cash_outflows', 0);
    }

    private function createAdmin(): User
    {
        return User::create([
            'name' => 'Admin Kas Keluar',
            'username' => 'admin-kas-keluar',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);
    }
}
