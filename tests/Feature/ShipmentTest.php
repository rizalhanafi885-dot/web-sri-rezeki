<?php

namespace Tests\Feature;

use App\Models\Purchase;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShipmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_shipment_details_and_print_a_surat_jalan(): void
    {
        $admin = $this->createAdmin();
        $this->createPurchase('TBS', 100);
        $this->createPurchase('Brondolan', 50);

        $response = $this->actingAs($admin)->post(route('admin.shipments.store'), [
            'tanggal' => '2026-10-14',
            'pks_tujuan' => 'SLUM',
            'nopol' => 'BG 2257 SU',
            'driver' => 'Angga',
            'tbs_kg' => 80,
            'brondolan_kg' => 20,
            'brd_afkir_kg' => 0,
            'buah_kecil_kg' => 0,
            'harga_jual_per_kg' => 4500,
            'catatan' => 'Pengiriman pagi',
        ]);

        $response->assertRedirect(route('admin.shipments.index'));
        $shipment = Shipment::firstOrFail();
        $this->assertSame('100.00', $shipment->total_kg);
        $this->assertSame('80.00', $shipment->tbs_kg);
        $this->assertSame('20.00', $shipment->brondolan_kg);
        $this->assertSame('450000.00', $shipment->total_pendapatan);
        $this->assertStringStartsWith('SJ-261014-', $shipment->no_nota);

        $this->actingAs($admin)
            ->get(route('admin.shipments.print', $shipment))
            ->assertOk()
            ->assertSee('SURAT JALAN PENGIRIMAN')
            ->assertSee('BG 2257 SU')
            ->assertSee('100,00 Kg');

        $this->actingAs($admin)
            ->get(route('admin.shipments.index'))
            ->assertOk()
            ->assertSee('Rincian Muatan (Kg)')
            ->assertSee('Harga Jual per Kg (Rp)')
            ->assertSee('Rp 450.000')
            ->assertSee('20,00')
            ->assertDontSee('Hapus');

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('20 Kg');
    }

    public function test_shipment_cannot_exceed_available_category_stock(): void
    {
        $admin = $this->createAdmin();
        $this->createPurchase('TBS', 100);

        $response = $this->actingAs($admin)->from(route('admin.shipments.index'))
            ->post(route('admin.shipments.store'), [
                'tanggal' => '2026-10-14',
                'pks_tujuan' => 'SLUM',
                'nopol' => 'BG 2257 SU',
                'driver' => 'Angga',
                'tbs_kg' => 100.01,
                'brondolan_kg' => 0,
                'brd_afkir_kg' => 0,
                'buah_kecil_kg' => 0,
                'harga_jual_per_kg' => 4500,
            ]);

        $response->assertRedirect(route('admin.shipments.index'))
            ->assertSessionHasErrors('tbs_kg');
        $this->assertDatabaseCount('shipments', 0);
    }

    public function test_edit_recalculates_total_and_does_not_count_the_shipment_against_itself(): void
    {
        $admin = $this->createAdmin();
        $this->createPurchase('TBS', 100);
        $shipment = Shipment::create([
            'no_nota' => 'SJ-261014-001',
            'tanggal' => '2026-10-14',
            'pks_tujuan' => 'SLUM',
            'nopol' => 'BG 2257 SU',
            'driver' => 'Angga',
            'tbs_kg' => 80,
            'brondolan_kg' => 0,
            'brd_afkir_kg' => 0,
            'buah_kecil_kg' => 0,
            'total_kg' => 80,
            'harga_jual_per_kg' => 4000,
            'total_pendapatan' => 320000,
        ]);

        $this->actingAs($admin)->put(route('admin.shipments.update', $shipment), [
            'tanggal' => '2026-10-14',
            'pks_tujuan' => 'SLUM',
            'nopol' => 'BG 2257 SU',
            'driver' => 'Angga',
            'tbs_kg' => 100,
            'brondolan_kg' => 0,
            'brd_afkir_kg' => 0,
            'buah_kecil_kg' => 0,
            'harga_jual_per_kg' => 5000,
        ])->assertRedirect(route('admin.shipments.index'));

        $this->assertSame('100.00', $shipment->fresh()->total_kg);
        $this->assertSame('500000.00', $shipment->fresh()->total_pendapatan);
    }

    public function test_stock_information_shows_database_totals_and_weighted_average_valuation(): void
    {
        $admin = $this->createAdmin();
        $this->createPurchase('TBS', 100, 90, 2000);
        Shipment::create([
            'no_nota' => 'SJ-261014-001',
            'tanggal' => '2026-10-14',
            'pks_tujuan' => 'SLUM',
            'nopol' => 'BG 2257 SU',
            'driver' => 'Angga',
            'tbs_kg' => 30,
            'brondolan_kg' => 0,
            'brd_afkir_kg' => 0,
            'buah_kecil_kg' => 0,
            'total_kg' => 30,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.stocks.index'))
            ->assertOk()
            ->assertSee('Informasi Stok')
            ->assertSee('100 Kg')
            ->assertSee('30 Kg')
            ->assertSee('70 Kg')
            ->assertSee('Rp 126.000')
            ->assertSee('TOTAL STOK BARANG (NETTO 1 TERSEDIA)')
            ->assertSee('TOTAL NILAI STOK (NETTO 2 × HARGA/KG)');
    }

    private function createAdmin(): User
    {
        return User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);
    }

    private function createPurchase(
        string $category,
        float $weight,
        ?float $netto2 = null,
        float $pricePerKg = 3000
    ): Purchase {
        $netto2 ??= $weight;

        return Purchase::create([
            'no_nota' => 'BL-'.uniqid(),
            'tanggal' => '2026-10-14',
            'petani' => 'Petani Test',
            'kategori' => $category,
            'harga_per_kg' => $pricePerKg,
            'bruto' => $weight,
            'tara' => 0,
            'netto_1' => $weight,
            'rafaksi' => 0,
            'netto_2' => $netto2,
            'upah_bongkar' => 0,
            'total_pembelian' => $netto2 * $pricePerKg,
            'total_bayar' => $netto2 * $pricePerKg,
        ]);
    }
}
