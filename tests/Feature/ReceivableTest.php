<?php

namespace Tests\Feature;

use App\Models\Receivable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceivableTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_receivable_and_view_its_detail(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.receivables.store'), [
            'pelanggan' => 'Yono',
            'tanggal' => '2026-10-14',
            'nominal_awal' => 1000000,
            'catatan' => 'Pinjaman modal',
        ]);

        $response->assertRedirect(route('admin.receivables.index'));
        $receivable = Receivable::firstOrFail();
        $this->assertSame('PTG-261014-001', $receivable->no_transaksi);
        $this->assertSame('Admin Piutang', $receivable->pemberi_pinjaman);
        $this->assertDatabaseHas('cash_outflows', [
            'no_transaksi' => 'KK-261014-001',
            'kategori' => 'Pencairan Piutang - Yono',
            'nominal' => 1000000,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.receivables.index'))
            ->assertOk()
            ->assertSee('Manajemen Piutang Pelanggan / Petani')
            ->assertSee('PTG-261014-001')
            ->assertSee('Rp 1.000.000')
            ->assertSee('Belum Lunas');

        $this->actingAs($admin)
            ->get(route('admin.receivables.json', $receivable))
            ->assertOk()
            ->assertJsonPath('data.pelanggan', 'Yono')
            ->assertJsonPath('data.sisa_utang', 1000000);
    }

    public function test_additional_loan_and_installment_payment_are_saved_and_update_balance(): void
    {
        $admin = $this->createAdmin();
        $receivable = $this->createReceivable($admin);

        $this->actingAs($admin)->post(
            route('admin.receivables.loans.store', $receivable),
            [
                '_form_context' => 'loan',
                'receivable_id' => $receivable->id,
                'tanggal' => '2026-10-15',
                'nominal' => 200000,
                'catatan' => 'Tambahan kebutuhan',
            ]
        )->assertRedirect(route('admin.receivables.index'));

        $this->actingAs($admin)->post(
            route('admin.receivables.payments.store', $receivable),
            [
                '_form_context' => 'payment',
                'receivable_id' => $receivable->id,
                'tanggal' => '2026-10-16',
                'nominal' => 300000,
                'metode' => 'Transfer',
                'catatan' => 'Pembayaran pertama',
            ]
        )->assertRedirect(route('admin.receivables.index'));

        $this->assertDatabaseCount('receivable_transactions', 2);
        $this->assertDatabaseCount('cash_outflows', 1);
        $this->assertDatabaseHas('cash_outflows', [
            'kategori' => 'Pencairan Piutang - Yono',
            'nominal' => 200000,
        ]);
        $this->assertDatabaseHas('receivable_transactions', [
            'receivable_id' => $receivable->id,
            'jenis' => 'loan',
            'nominal' => 200000,
            'metode' => 'Pinjaman Tambahan',
        ]);
        $this->assertDatabaseHas('receivable_transactions', [
            'receivable_id' => $receivable->id,
            'jenis' => 'payment',
            'nominal' => 300000,
            'metode' => 'Transfer',
        ]);
        $this->assertSame(900000.0, $receivable->fresh()->sisa_utang);
        $this->assertSame('Cicilan', $receivable->fresh()->status);

        $this->actingAs($admin)
            ->get(route('admin.receivables.json', $receivable))
            ->assertJsonPath('data.total_tambahan', 200000)
            ->assertJsonPath('data.total_dibayar', 300000)
            ->assertJsonPath('data.sisa_utang', 900000)
            ->assertJsonPath('data.transactions.1.metode', 'Transfer');
    }

    public function test_installment_cannot_exceed_remaining_balance(): void
    {
        $admin = $this->createAdmin();
        $receivable = $this->createReceivable($admin, 100000);

        $response = $this->actingAs($admin)->from(route('admin.receivables.index'))
            ->post(route('admin.receivables.payments.store', $receivable), [
                '_form_context' => 'payment',
                'receivable_id' => $receivable->id,
                'tanggal' => '2026-10-14',
                'nominal' => 100000.01,
                'metode' => 'Tunai',
            ]);

        $response->assertRedirect(route('admin.receivables.index'))
            ->assertSessionHasErrors('nominal');
        $this->assertDatabaseCount('receivable_transactions', 0);
        $this->assertSame(100000.0, $receivable->fresh()->sisa_utang);
    }

    public function test_receivable_list_can_filter_by_customer_and_status(): void
    {
        $admin = $this->createAdmin();
        $this->createReceivable($admin, 100000, 'Yono');
        $this->createReceivable($admin, 200000, 'Budi');

        $this->actingAs($admin)
            ->get(route('admin.receivables.index', ['q' => 'Yono', 'status' => 'Belum Lunas']))
            ->assertOk()
            ->assertSee('Yono')
            ->assertDontSee('Budi');
    }

    private function createAdmin(): User
    {
        return User::create([
            'name' => 'Admin Piutang',
            'username' => 'admin-piutang',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);
    }

    private function createReceivable(User $admin, float $amount = 1000000, string $customer = 'Yono'): Receivable
    {
        return Receivable::create([
            'no_transaksi' => 'PTG-'.uniqid(),
            'tanggal' => '2026-10-14',
            'pelanggan' => $customer,
            'pemberi_pinjaman' => $admin->name,
            'nominal_awal' => $amount,
        ]);
    }
}
