<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_flow_updates_invoice_status(): void
    {
        $role = Role::factory()->admin()->create();
        $user = User::factory()->create(['role_id' => $role->id]);
        $customer = Customer::factory()->create();

        $sale = Sale::create([
            'sale_number' => 'V-TEST-0001',
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'sale_date' => now()->toDateString(),
            'sold_at' => now(),
            'sale_type' => 'vente',
            'discount_amount' => 0,
            'subtotal_ht' => 0,
            'total_ttc' => 100000,
            'status' => 'validated',
        ]);

        $invoice = Invoice::create([
            'invoice_number' => 'F-TEST-0001',
            'sale_id' => $sale->id,
            'customer_id' => $customer->id,
            'issued_at' => now()->toDateString(),
            'subtotal_ht' => 0,
            'total_ttc' => 100000,
            'status' => InvoiceStatus::Issued,
        ]);

        $service = app(PaymentService::class);

        // Paiement partiel
        $service->store($invoice, ['amount' => 40000, 'method' => 'wave', 'paid_at' => now()->toDateString()], $user->id);
        $invoice->refresh();
        $this->assertEquals(InvoiceStatus::Partial, $invoice->status);
        $this->assertEquals(60000, $invoice->remaining_amount);

        // Paiement complémentaire qui solde la facture
        $payment2 = $service->store($invoice, ['amount' => 60000, 'method' => 'cash', 'paid_at' => now()->toDateString()], $user->id);
        $invoice->refresh();
        $this->assertEquals(InvoiceStatus::Paid, $invoice->status);
        $this->assertEquals(0, $invoice->remaining_amount);

        // Tentative de paiement sur facture déjà payée
        $this->expectException(\RuntimeException::class);
        $service->store($invoice, ['amount' => 1000, 'method' => 'cash', 'paid_at' => now()->toDateString()], $user->id);
    }

    public function test_deleting_payment_reverts_status(): void
    {
        $role = Role::factory()->admin()->create();
        $user = User::factory()->create(['role_id' => $role->id]);
        $customer = Customer::factory()->create();

        $sale = Sale::create([
            'sale_number' => 'V-TEST-0002',
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'sale_date' => now()->toDateString(),
            'sold_at' => now(),
            'sale_type' => 'vente',
            'discount_amount' => 0,
            'subtotal_ht' => 0,
            'total_ttc' => 50000,
            'status' => 'validated',
        ]);

        $invoice = Invoice::create([
            'invoice_number' => 'F-TEST-0002',
            'sale_id' => $sale->id,
            'customer_id' => $customer->id,
            'issued_at' => now()->toDateString(),
            'subtotal_ht' => 0,
            'total_ttc' => 50000,
            'status' => InvoiceStatus::Issued,
        ]);

        $service = app(PaymentService::class);
        $payment = $service->store($invoice, ['amount' => 50000, 'method' => 'orange_money', 'paid_at' => now()->toDateString()], $user->id);

        $invoice->refresh();
        $this->assertEquals(InvoiceStatus::Paid, $invoice->status);

        $service->destroy($payment);
        $invoice->refresh();
        $this->assertEquals(InvoiceStatus::Issued, $invoice->status);
        $this->assertEquals(50000, $invoice->remaining_amount);
    }
}
