<?php

namespace Tests\Feature;

use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseRafaksiTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_accepts_rafaksi_with_one_decimal_place(): void
    {
        $this->actingAs($this->createAdmin())
            ->post(route('admin.purchases.store'), $this->purchaseData('12.5'))
            ->assertRedirect(route('admin.purchases.index'));

        $this->assertDatabaseHas('purchases', ['rafaksi' => 12.5]);
    }

    public function test_purchase_rejects_rafaksi_with_more_than_one_decimal_place(): void
    {
        $this->actingAs($this->createAdmin())
            ->from(route('admin.purchases.index'))
            ->post(route('admin.purchases.store'), $this->purchaseData('12.55'))
            ->assertRedirect(route('admin.purchases.index'))
            ->assertSessionHasErrors('rafaksi');

        $this->assertDatabaseCount('purchases', 0);
    }

    public function test_purchase_update_rejects_rafaksi_with_more_than_one_decimal_place(): void
    {
        $admin = $this->createAdmin();
        $purchase = Purchase::create(array_merge($this->purchaseData('7'), [
            'no_nota' => 'BL-261014-001',
        ]));

        $this->actingAs($admin)
            ->from(route('admin.purchases.index'))
            ->put(route('admin.purchases.update', $purchase), $this->purchaseData('12.55'))
            ->assertRedirect(route('admin.purchases.index'))
            ->assertSessionHasErrors('rafaksi');

        $this->assertSame(7.0, $purchase->fresh()->rafaksi);
    }

    public function test_purchase_defaults_blank_upah_bongkar_to_zero(): void
    {
        $data = $this->purchaseData('7');
        $data['upah_bongkar'] = '';

        $this->actingAs($this->createAdmin())
            ->post(route('admin.purchases.store'), $data)
            ->assertRedirect(route('admin.purchases.index'));

        $this->assertDatabaseHas('purchases', ['upah_bongkar' => 0]);
    }

    public function test_purchase_rejects_decimal_upah_bongkar(): void
    {
        $data = $this->purchaseData('7');
        $data['upah_bongkar'] = '8888.88';

        $this->actingAs($this->createAdmin())
            ->from(route('admin.purchases.index'))
            ->post(route('admin.purchases.store'), $data)
            ->assertRedirect(route('admin.purchases.index'))
            ->assertSessionHasErrors('upah_bongkar');

        $this->assertDatabaseCount('purchases', 0);
    }

    private function createAdmin(): User
    {
        return User::create([
            'name' => 'Admin Pembelian',
            'username' => 'admin-pembelian',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);
    }

    private function purchaseData(string $rafaksi): array
    {
        return [
            'tanggal' => '2026-10-14',
            'petani' => 'Petani Test',
            'kategori' => 'TBS',
            'harga_per_kg' => 3000,
            'bruto' => 1000,
            'tara' => 0,
            'netto_1' => 1000,
            'rafaksi' => $rafaksi,
            'netto_2' => 875,
            'upah_bongkar' => 0,
            'total_pembelian' => 2625000,
            'total_bayar' => 2625000,
        ];
    }
}
