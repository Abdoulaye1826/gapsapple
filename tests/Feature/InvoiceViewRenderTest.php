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
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class InvoiceViewRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_edit_view_renders_with_payments(): void
    {
        View::share('errors', new ViewErrorBag);

        $role = Role::factory()->admin()->create();
        $user = User::factory()->create(['role_id' => $role->id]);
        $customer = Customer::factory()->create();

        $sale = Sale::create([
            'sale_number' => 'V-RENDER-0001',
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'sale_date' => now()->toDateString(),
            'sold_at' => now(),
            'sale_type' => 'vente',
            'discount_amount' => 0,
            'subtotal_ht' => 0,
            'total_ttc' => 75000,
            'status' => 'validated',
        ]);

        $invoice = Invoice::create([
            'invoice_number' => 'F-RENDER-0001',
            'sale_id' => $sale->id,
            'customer_id' => $customer->id,
            'issued_at' => now()->toDateString(),
            'subtotal_ht' => 0,
            'total_ttc' => 75000,
            'status' => InvoiceStatus::Issued,
        ]);

        app(PaymentService::class)->store($invoice, ['amount' => 30000, 'method' => 'wave', 'paid_at' => now()->toDateString()], $user->id);

        $this->actingAs($user);
        $invoice->load(['payments.recordedBy']);

        $html = view('invoices.edit', [
            'invoice' => $invoice,
            'customers' => Customer::all(),
            'sales' => Sale::all(),
        ])->render();

        $this->assertStringContainsString('Paiements', $html);
        $this->assertStringContainsString('Wave', $html);
        $this->assertStringContainsString('Partiellement payée', $html);
    }
}
