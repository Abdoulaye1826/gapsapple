<?php

namespace Tests\Feature;

use App\Enums\ImeiStatus;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductImei;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use App\Services\ProductImeiService;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImeiFlowTest extends TestCase
{
    use RefreshDatabase;

    private function makePhone(string $reference = 'IMEI-TEST'): Product
    {
        $category = Category::factory()->create();

        return Product::create([
            'category_id' => $category->id,
            'reference' => $reference,
            'name' => 'Téléphone test',
            'purchase_price' => 400000,
            'sale_price' => 600000,
            'stock_quantity' => 0,
            'minimum_stock' => 1,
            'is_active' => true,
            'tracks_imei' => true,
        ]);
    }

    public function test_adding_imeis_syncs_stock_and_rejects_duplicates(): void
    {
        $phone = $this->makePhone();
        $service = app(ProductImeiService::class);

        $service->store($phone, ['356789123456781', '356789123456782', '356789123456783']);
        $this->assertEquals(3, $phone->fresh()->stock_quantity);

        $this->expectException(\RuntimeException::class);
        $service->store($phone, ['356789123456781']);
    }

    public function test_selling_an_imei_marks_it_sold_and_decrements_stock(): void
    {
        $role = Role::factory()->admin()->create();
        $user = User::factory()->create(['role_id' => $role->id]);
        $customer = Customer::factory()->create();
        $phone = $this->makePhone();

        app(ProductImeiService::class)->store($phone, ['356789123456781', '356789123456782']);

        $saleService = app(SaleService::class);
        $sale = $saleService->create([
            'sale_type' => 'vente',
            'customer_id' => $customer->id,
            'product_id' => [$phone->id],
            'quantity' => [1],
            'unit_price' => [600000],
            'imei' => ['356789123456781'],
            'discount_amount' => 0,
            'status' => 'validated',
        ], $user->id);

        $phone->refresh();
        $this->assertEquals(1, $phone->stock_quantity);

        $imei = ProductImei::where('imei', '356789123456781')->first();
        $this->assertEquals(ImeiStatus::Sold, $imei->status);
        $this->assertEquals($sale->id, $imei->sale_id);
        $this->assertNotNull($imei->sold_at);

        // Revente du même IMEI : rejetée.
        $this->expectException(\RuntimeException::class);
        $saleService->create([
            'sale_type' => 'vente',
            'customer_id' => $customer->id,
            'product_id' => [$phone->id],
            'quantity' => [1],
            'unit_price' => [600000],
            'imei' => ['356789123456781'],
            'discount_amount' => 0,
            'status' => 'validated',
        ], $user->id);
    }

    public function test_reverting_a_sale_releases_the_imei_and_restores_stock(): void
    {
        $role = Role::factory()->admin()->create();
        $user = User::factory()->create(['role_id' => $role->id]);
        $customer = Customer::factory()->create();
        $phone = $this->makePhone();

        app(ProductImeiService::class)->store($phone, ['356789123456781']);

        $saleService = app(SaleService::class);
        $sale = $saleService->create([
            'sale_type' => 'vente',
            'customer_id' => $customer->id,
            'product_id' => [$phone->id],
            'quantity' => [1],
            'unit_price' => [600000],
            'imei' => ['356789123456781'],
            'discount_amount' => 0,
            'status' => 'validated',
        ], $user->id);

        $this->assertEquals(0, $phone->fresh()->stock_quantity);

        $saleService->update($sale, [
            'sale_type' => 'vente',
            'customer_id' => $customer->id,
            'product_id' => [$phone->id],
            'quantity' => [1],
            'unit_price' => [600000],
            'imei' => ['356789123456781'],
            'discount_amount' => 0,
            'status' => 'draft',
        ], $user->id);

        $phone->refresh();
        $imei = ProductImei::where('imei', '356789123456781')->first();

        $this->assertEquals(1, $phone->stock_quantity);
        $this->assertEquals(ImeiStatus::Available, $imei->status);
        $this->assertNull($imei->sale_id);
    }

    public function test_exchange_receives_imei_and_adds_it_to_stock(): void
    {
        $role = Role::factory()->admin()->create();
        $user = User::factory()->create(['role_id' => $role->id]);
        $customer = Customer::factory()->create();
        $phone = $this->makePhone('IMEI-EXCHANGE');

        $normalCategory = Category::factory()->create();
        $normalProduct = Product::create([
            'category_id' => $normalCategory->id,
            'reference' => 'NORMAL-PRODUCT',
            'name' => 'Accessoire',
            'purchase_price' => 1000,
            'sale_price' => 2000,
            'stock_quantity' => 5,
            'minimum_stock' => 1,
            'is_active' => true,
        ]);

        $saleService = app(SaleService::class);
        $saleService->create([
            'sale_type' => 'echange',
            'customer_id' => $customer->id,
            'product_id' => [$normalProduct->id],
            'quantity' => [1],
            'unit_price' => [2000],
            'exchange_product_id' => $phone->id,
            'exchange_quantity' => 1,
            'exchange_imei' => '999988887777666',
            'exchange_added_amount' => 0,
            'discount_amount' => 0,
            'status' => 'validated',
        ], $user->id);

        $phone->refresh();
        $imei = ProductImei::where('imei', '999988887777666')->first();

        $this->assertEquals(1, $phone->stock_quantity);
        $this->assertEquals(ImeiStatus::Available, $imei->status);
        $this->assertNotNull($imei->exchange_sale_id);
    }
}
