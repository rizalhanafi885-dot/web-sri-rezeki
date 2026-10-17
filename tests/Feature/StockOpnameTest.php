<?php

namespace Tests\Feature;

use App\Models\Purchase;
use App\Models\Shipment;
use App\Models\StockOpname;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockOpnameTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_record_stock_opname_and_adjust_available_stock(): void
    {
        $admin = $this->createAdmin();
        $this->createPurchase('TBS', 100);
        $this->createShipment(20);

        $response = $this->actingAs($admin)->post(route('admin.stock-opnames.store'), [
            'tanggal' => '2026-10-14',
            'kategori' => 'TBS',
            'stok_fisik_kg' => 75,
        ]);

        $response->assertRedirect(route('admin.stock-opnames.index'));
        $this->assertDatabaseHas('stock_opnames', [
            'kategori' => 'TBS',
            'stok_sistem_kg' => 80,
            'stok_fisik_kg' => 75,
            'selisih_kg' => -5,
            'petugas' => 'Admin Opname',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.stock-opnames.index'))
            ->assertOk()
            ->assertSee('Stok Opname (Penyesuaian)')
            ->assertSee('80 Kg')
            ->assertSee('75 Kg')
            ->assertSee('-5 Kg')
            ->assertSee('Admin Opname')
            ->assertDontSee('Hapus');

        $this->assertSame(75.0, Shipment::availableStock()['TBS']);

        $this->actingAs($admin)
            ->get(route('admin.stocks.index'))
            ->assertOk()
            ->assertSee('Penyesuaian')
            ->assertSee('75 Kg');
    }

    public function test_admin_can_edit_opname_and_recalculate_its_stock_adjustment(): void
    {
        $admin = $this->createAdmin();
        $this->createPurchase('TBS', 100);
        $opname = StockOpname::create([
            'tanggal' => '2026-10-14',
            'kategori' => 'TBS',
            'stok_sistem_kg' => 100,
            'stok_fisik_kg' => 90,
            'selisih_kg' => -10,
            'petugas' => $admin->name,
        ]);

        $response = $this->actingAs($admin)->put(
            route('admin.stock-opnames.update', $opname),
            [
                'tanggal' => '2026-10-14',
                'kategori' => 'TBS',
                'stok_fisik_kg' => 95,
            ]
        );

        $response->assertRedirect(route('admin.stock-opnames.index'));
        $this->assertSame('100.00', $opname->fresh()->stok_sistem_kg);
        $this->assertSame('95.00', $opname->fresh()->stok_fisik_kg);
        $this->assertSame('-5.00', $opname->fresh()->selisih_kg);
        $this->assertSame(95.0, Shipment::availableStock()['TBS']);
    }

    public function test_stock_opname_requires_a_supported_category_and_non_negative_physical_stock(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->from(route('admin.stock-opnames.index'))
            ->post(route('admin.stock-opnames.store'), [
                'tanggal' => '2026-10-14',
                'kategori' => 'Unknown',
                'stok_fisik_kg' => -1,
            ])
            ->assertRedirect(route('admin.stock-opnames.index'))
            ->assertSessionHasErrors(['kategori', 'stok_fisik_kg']);

        $this->assertDatabaseCount('stock_opnames', 0);
    }

    private function createAdmin(): User
    {
        return User::create([
            'name' => 'Admin Opname',
            'username' => 'admin-opname',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);
    }

    private function createPurchase(string $category, float $weight): Purchase
    {
        return Purchase::create([
            'no_nota' => 'BL-'.uniqid(),
            'tanggal' => '2026-10-14',
            'petani' => 'Petani Test',
            'kategori' => $category,
            'harga_per_kg' => 3000,
            'bruto' => $weight,
            'tara' => 0,
            'netto_1' => $weight,
            'rafaksi' => 0,
            'netto_2' => $weight,
            'upah_bongkar' => 0,
            'total_pembelian' => $weight * 3000,
            'total_bayar' => $weight * 3000,
        ]);
    }

    private function createShipment(float $weight): Shipment
    {
        return Shipment::create([
            'no_nota' => 'SJ-'.uniqid(),
            'tanggal' => '2026-10-14',
            'pks_tujuan' => 'SLUM',
            'nopol' => 'BG 2257 SU',
            'driver' => 'Angga',
            'tbs_kg' => $weight,
            'brondolan_kg' => 0,
            'brd_afkir_kg' => 0,
            'buah_kecil_kg' => 0,
            'total_kg' => $weight,
        ]);
    }
}
