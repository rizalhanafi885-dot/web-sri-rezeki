<?php

namespace Tests\Feature;

use App\Models\CashInflow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashInflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_and_save_cash_inflow(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->get(route('admin.cash-inflows.index'))
            ->assertOk()
            ->assertSee('Pencatatan Kas Masuk')
            ->assertSee('+ Tambah Pemasukan');

        $response = $this->actingAs($admin)->post(route('admin.cash-inflows.store'), [
            'tanggal' => '2026-10-14',
            'sumber' => 'PUSAT',
            'nominal' => 20000000,
            'keterangan' => 'Terima Modal OP',
        ]);

        $response->assertRedirect(route('admin.cash-inflows.index'));
        $inflow = CashInflow::firstOrFail();
        $this->assertSame('KM-261014-001', $inflow->no_transaksi);
        $this->assertDatabaseHas('cash_inflows', [
            'no_transaksi' => 'KM-261014-001',
            'sumber' => 'PUSAT',
            'nominal' => 20000000,
            'keterangan' => 'Terima Modal OP',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.cash-inflows.index'))
            ->assertOk()
            ->assertSee('KM-261014-001')
            ->assertSee('Contoh: 1.000.000')
            ->assertSee('PUSAT')
            ->assertSee('Terima Modal OP')
            ->assertSee('Rp 20.000.000')
            ->assertSee('Edit')
            ->assertDontSee('Hapus');
    }

    public function test_admin_can_edit_cash_inflow_without_changing_transaction_number(): void
    {
        $admin = $this->createAdmin();
        $inflow = CashInflow::create([
            'no_transaksi' => 'KM-261014-001',
            'tanggal' => '2026-10-14',
            'sumber' => 'PUSAT',
            'nominal' => 20000000,
            'keterangan' => 'Modal awal',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.cash-inflows.update', $inflow), [
                'tanggal' => '2026-10-15',
                'sumber' => 'Penjualan',
                'nominal' => 1250000,
                'keterangan' => 'Koreksi pemasukan',
            ])
            ->assertRedirect(route('admin.cash-inflows.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('cash_inflows', [
            'id' => $inflow->id,
            'no_transaksi' => 'KM-261014-001',
            'sumber' => 'Penjualan',
            'nominal' => 1250000,
            'keterangan' => 'Koreksi pemasukan',
        ]);
        $this->assertSame('2026-10-15', $inflow->fresh()->tanggal->format('Y-m-d'));
        $this->assertDatabaseCount('cash_inflows', 1);
    }

    public function test_cash_inflow_edit_rejects_decimal_amount_and_keeps_original_data(): void
    {
        $admin = $this->createAdmin();
        $inflow = CashInflow::create([
            'no_transaksi' => 'KM-261014-001',
            'tanggal' => '2026-10-14',
            'sumber' => 'PUSAT',
            'nominal' => 20000000,
            'keterangan' => 'Modal awal',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.cash-inflows.index'))
            ->put(route('admin.cash-inflows.update', $inflow), [
                'tanggal' => '2026-10-15',
                'sumber' => 'Penjualan',
                'nominal' => '1250.50',
                'keterangan' => 'Koreksi pemasukan',
                '_cash_inflow_id' => $inflow->id,
            ])
            ->assertRedirect(route('admin.cash-inflows.index'))
            ->assertSessionHasErrors('nominal');

        $this->assertSame('PUSAT', $inflow->fresh()->sumber);
        $this->assertSame('20000000.00', $inflow->fresh()->nominal);
    }

    public function test_cash_inflow_rejects_non_positive_amount_and_missing_fields(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->from(route('admin.cash-inflows.index'))
            ->post(route('admin.cash-inflows.store'), [
                'tanggal' => '2026-10-14',
                'sumber' => 'PUSAT',
                'nominal' => 0,
                'keterangan' => '',
            ])
            ->assertRedirect(route('admin.cash-inflows.index'))
            ->assertSessionHasErrors(['nominal', 'keterangan']);

        $this->assertDatabaseCount('cash_inflows', 0);
    }

    public function test_cash_inflow_nominal_must_be_a_whole_rupiah_amount(): void
    {
        $this->actingAs($this->createAdmin())
            ->from(route('admin.cash-inflows.index'))
            ->post(route('admin.cash-inflows.store'), [
                'tanggal' => '2026-10-14',
                'sumber' => 'PUSAT',
                'nominal' => '7777.77',
                'keterangan' => 'Modal',
            ])
            ->assertRedirect(route('admin.cash-inflows.index'))
            ->assertSessionHasErrors('nominal');

        $this->assertDatabaseCount('cash_inflows', 0);
    }

    private function createAdmin(): User
    {
        return User::create([
            'name' => 'Admin Kas',
            'username' => 'admin-kas',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);
    }
}
