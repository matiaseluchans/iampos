<?php

namespace Tests\Feature;

use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Corre contra la BD "testing" (nunca contra la de desarrollo) y revierte cada test.
 */
class PurchaseModuleTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;
    private User $otherUser;
    private Warehouse $warehouse;
    private Product $product;
    private PaymentMethod $method;
    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::connection()->getDatabaseName() !== 'testing') {
            $this->fail('Estos tests solo pueden correr contra la BD "testing".');
        }

        // El tenant 1 es "superadmin" para TenantScope: se reserva para que A y B sean tenants comunes.
        if (!DB::table('tenants')->where('id', 1)->exists()) {
            DB::table('tenants')->insert(['id' => 1, 'slug' => 'root', 'name' => 'Root', 'created_at' => now(), 'updated_at' => now()]);
        }
        $tenantA = DB::table('tenants')->insertGetId(['slug' => 'a', 'name' => 'A', 'created_at' => now(), 'updated_at' => now()]);
        $tenantB = DB::table('tenants')->insertGetId(['slug' => 'b', 'name' => 'B', 'created_at' => now(), 'updated_at' => now()]);

        $this->user = User::create(['name' => 'User A', 'email' => 'a@test.local', 'password' => bcrypt('x'), 'tenant_id' => $tenantA, 'active' => 1]);
        $this->otherUser = User::create(['name' => 'User B', 'email' => 'b@test.local', 'password' => bcrypt('x'), 'tenant_id' => $tenantB, 'active' => 1]);

        Sanctum::actingAs($this->user);

        $this->warehouse = Warehouse::create(['name' => 'Central', 'active' => true]);
        $this->product = Product::create(['name' => 'Coca 1.5', 'purchase_price' => 100, 'sale_price' => 150, 'active' => 1]);
        Stock::create(['product_id' => $this->product->id, 'warehouse_id' => $this->warehouse->id, 'quantity' => 10]);
        $this->method = PaymentMethod::create(['name' => 'Efectivo', 'active' => 1]);
        $this->supplier = Supplier::create(['name' => 'Distribuidora Sur', 'payment_term_days' => 15]);
    }

    private function purchasePayload(array $overrides = []): array
    {
        return array_merge([
            'supplier_id' => $this->supplier->id,
            'purchase_date' => '02/11/2026',
            'warehouse_id' => $this->warehouse->id,
            'supplier_document' => 'A-0001-00001234',
            'due_date' => '17/11/2026',
            'discount_amount' => 0,
            'aditional' => 0,
            'update_cost' => false,
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 5, 'unit_cost' => 120],
            ],
        ], $overrides);
    }

    private function createPurchase(array $overrides = []): array
    {
        $response = $this->postJson('/api/purchases', $this->purchasePayload($overrides));
        $response->assertStatus(201);

        return $response->json('data');
    }

    private function stockQuantity(): float
    {
        return (float) Stock::where('product_id', $this->product->id)->where('warehouse_id', $this->warehouse->id)->value('quantity');
    }

    private function payload(Purchase|int $purchase, float $amount): array
    {
        return [
            'purchase_id' => $purchase instanceof Purchase ? $purchase->id : $purchase,
            'payments' => [
                ['payment_method_id' => ['id' => $this->method->id], 'amount' => $amount, 'payment_date' => '03/11/2026'],
            ],
        ];
    }

    // ---------------------------------------------------------------- Proveedores

    public function test_supplier_requires_name(): void
    {
        $this->postJson('/api/suppliers', ['name' => ''])->assertStatus(422);

        $this->assertSame(1, Supplier::count());
    }

    public function test_supplier_tax_id_must_be_unique_within_tenant(): void
    {
        $this->postJson('/api/suppliers', ['name' => 'Uno', 'tax_id' => '30-71126035-4'])->assertStatus(201);
        $this->postJson('/api/suppliers', ['name' => 'Dos', 'tax_id' => '30-71126035-4'])->assertStatus(422);
    }

    public function test_supplier_update_and_toggle_active_use_data_envelope(): void
    {
        $this->postJson("/api/suppliers/{$this->supplier->id}", ['_method' => 'PUT', 'data' => ['active' => 0]])
            ->assertOk();

        $this->assertEquals(0, $this->supplier->fresh()->active);
        $this->assertSame('Distribuidora Sur', $this->supplier->fresh()->name);
    }

    public function test_front_style_delete_via_post_with_method_override(): void
    {
        $this->postJson("/api/suppliers/{$this->supplier->id}", ['_method' => 'DELETE', 'data' => $this->supplier->id])
            ->assertOk();

        $this->assertNull(Supplier::find($this->supplier->id));
    }

    public function test_cannot_delete_supplier_with_purchases(): void
    {
        $this->createPurchase();

        $this->deleteJson("/api/suppliers/{$this->supplier->id}")->assertStatus(422);

        $this->assertNotNull(Supplier::find($this->supplier->id));
    }

    public function test_can_delete_supplier_without_purchases(): void
    {
        $this->deleteJson("/api/suppliers/{$this->supplier->id}")->assertOk();

        $this->assertNull(Supplier::find($this->supplier->id));
    }

    public function test_statement_balance_equals_total_minus_paid(): void
    {
        $first = $this->createPurchase();                                 // 5 x 120 = 600
        $this->createPurchase(['items' => [['product_id' => $this->product->id, 'quantity' => 1, 'unit_cost' => 400]]]); // 400
        $this->postJson('/api/supplier-payments', $this->payload($first['purchase']['id'], 250))->assertStatus(201);

        $data = $this->getJson("/api/suppliers/{$this->supplier->id}/statement")->assertOk()->json('data');

        $this->assertEquals(1000, $data['total_purchased']);
        $this->assertEquals(250, $data['total_paid']);
        $this->assertEquals(750, $data['balance']);
        $this->assertCount(2, $data['purchases']);
    }

    // ---------------------------------------------------------------- Alta de compra

    public function test_creating_purchase_increases_stock_and_records_movement(): void
    {
        $data = $this->createPurchase();

        $this->assertEquals(15, $this->stockQuantity());
        $this->assertSame('0000000001', $data['purchase']['purchase_number']);
        $this->assertSame('received', $data['purchase']['status']);
        $this->assertSame('pending', $data['purchase']['payment_status']);

        $movement = Stock::where('product_id', $this->product->id)->first()->movements()->first();
        $this->assertSame('entrada', $movement->movement_type);
        $this->assertEquals(5, $movement->quantity);
        $this->assertEquals(10, $movement->previous_quantity);
        $this->assertEquals(15, $movement->new_quantity);
        $this->assertStringContainsString('0000000001', $movement->notes);
    }

    public function test_totals_are_recalculated_on_the_server(): void
    {
        $data = $this->createPurchase([
            'discount_amount' => 50,
            'aditional' => 20,
            'subtotal' => 1,          // valores del cliente: deben ignorarse
            'total_amount' => 1,
        ]);

        $this->assertEquals(600, $data['purchase']['subtotal']);
        $this->assertEquals(570, $data['purchase']['total_amount']);   // 600 - 50 + 20
        $this->assertEquals(1, $data['purchase']['quantity_products']);
    }

    public function test_creates_stock_row_when_product_has_none(): void
    {
        $newProduct = Product::create(['name' => 'Fanta', 'purchase_price' => 90, 'sale_price' => 130, 'active' => 1]);

        $this->createPurchase(['items' => [['product_id' => $newProduct->id, 'quantity' => 8, 'unit_cost' => 90]]]);

        $stock = Stock::where('product_id', $newProduct->id)->first();
        $this->assertNotNull($stock);
        $this->assertEquals(8, $stock->quantity);
        $this->assertEquals($this->warehouse->id, $stock->warehouse_id);
        $this->assertSame(1, $stock->movements()->count());
    }

    public function test_rejects_invalid_purchases_without_touching_stock(): void
    {
        $invalid = [
            'sin items' => ['items' => []],
            'cantidad cero' => ['items' => [['product_id' => $this->product->id, 'quantity' => 0, 'unit_cost' => 10]]],
            'costo negativo' => ['items' => [['product_id' => $this->product->id, 'quantity' => 1, 'unit_cost' => -1]]],
            'sin proveedor' => ['supplier_id' => null],
            'descuento mayor al subtotal' => ['discount_amount' => 100000],
        ];

        foreach ($invalid as $case => $override) {
            $this->postJson('/api/purchases', $this->purchasePayload($override))->assertStatus(422, $case);
        }

        $this->assertEquals(10, $this->stockQuantity());
        $this->assertSame(0, Purchase::count());
    }

    public function test_update_cost_sets_product_purchase_price(): void
    {
        $this->createPurchase(['update_cost' => true]);

        $this->assertEquals(120, $this->product->fresh()->purchase_price);
        $this->assertEquals(150, $this->product->fresh()->sale_price);
    }

    public function test_cost_is_not_updated_when_flag_is_off(): void
    {
        $this->createPurchase(['update_cost' => false]);

        $this->assertEquals(100, $this->product->fresh()->purchase_price);
    }

    public function test_purchase_numbers_are_sequential_per_tenant(): void
    {
        $this->createPurchase();
        $second = $this->createPurchase();
        $this->assertSame('0000000002', $second['purchase']['purchase_number']);

        Sanctum::actingAs($this->otherUser);
        $supplierB = Supplier::create(['name' => 'Proveedor B']);
        $warehouseB = Warehouse::create(['name' => 'Deposito B', 'active' => true]);
        $productB = Product::create(['name' => 'Producto B', 'purchase_price' => 1, 'sale_price' => 2, 'active' => 1]);
        $other = $this->postJson('/api/purchases', $this->purchasePayload([
            'supplier_id' => $supplierB->id,
            'warehouse_id' => $warehouseB->id,
            'items' => [['product_id' => $productB->id, 'quantity' => 1, 'unit_cost' => 1]],
        ]))->assertStatus(201)->json('data');

        $this->assertSame('0000000001', $other['purchase']['purchase_number']);
    }

    public function test_cannot_use_supplier_from_another_tenant(): void
    {
        Sanctum::actingAs($this->otherUser);
        $foreign = Supplier::create(['name' => 'Ajeno']);

        Sanctum::actingAs($this->user);
        $this->postJson('/api/purchases', $this->purchasePayload(['supplier_id' => $foreign->id]))->assertStatus(422);

        $this->assertEquals(10, $this->stockQuantity());
    }

    // ---------------------------------------------------------------- Pagos y anulación

    public function test_partial_then_full_payment_updates_status(): void
    {
        $purchase = Purchase::find($this->createPurchase()['purchase']['id']);   // total 600

        $this->postJson('/api/supplier-payments', $this->payload($purchase, 200))->assertStatus(201);
        $this->assertSame('partial', $purchase->fresh()->payment_status);
        $this->assertEquals(200, $purchase->fresh()->total_paid);

        $this->postJson('/api/supplier-payments', $this->payload($purchase, 400))->assertStatus(201);
        $this->assertSame('paid', $purchase->fresh()->payment_status);
        $this->assertEquals(600, $purchase->fresh()->total_paid);
    }

    public function test_dates_accept_optional_time_suffix_sent_by_the_ui(): void
    {
        $data = $this->createPurchase(['purchase_date' => '02/11/2026 10:35', 'due_date' => '17/11/2026 00:00']);
        $purchase = Purchase::find($data['purchase']['id']);
        $this->assertSame('2026-11-17', $purchase->due_date->format('Y-m-d'));

        $payload = $this->payload($purchase, 100);
        $payload['payments'][0]['payment_date'] = '03/11/2026 09:15';
        $this->postJson('/api/supplier-payments', $payload)->assertStatus(201);
    }

    public function test_invalid_dates_are_rejected(): void
    {
        $this->postJson('/api/purchases', $this->purchasePayload(['purchase_date' => '31/02/2026']))->assertStatus(422);
        $this->postJson('/api/purchases', $this->purchasePayload(['due_date' => 'mañana']))->assertStatus(422);
        $this->assertSame(0, Purchase::count());
    }

    public function test_payment_over_pending_balance_is_rejected(): void
    {
        $purchase = Purchase::find($this->createPurchase()['purchase']['id']);

        $this->postJson('/api/supplier-payments', $this->payload($purchase, 600.01))->assertStatus(422);

        $this->assertSame('pending', $purchase->fresh()->payment_status);
        $this->assertEquals(0, $purchase->fresh()->total_paid);
    }

    public function test_payment_must_be_positive(): void
    {
        $purchase = Purchase::find($this->createPurchase()['purchase']['id']);

        $this->postJson('/api/supplier-payments', $this->payload($purchase, 0))->assertStatus(422);
        $this->postJson('/api/supplier-payments', $this->payload($purchase, -5))->assertStatus(422);

        $this->assertEquals(0, $purchase->fresh()->total_paid);
    }

    public function test_voiding_payment_recomputes_status(): void
    {
        $purchase = Purchase::find($this->createPurchase()['purchase']['id']);
        $this->postJson('/api/supplier-payments', $this->payload($purchase, 600))->assertStatus(201);
        $this->assertSame('paid', $purchase->fresh()->payment_status);

        $paymentId = $purchase->payments()->first()->id;
        $this->deleteJson("/api/supplier-payments/{$paymentId}")->assertOk();

        $this->assertSame('pending', $purchase->fresh()->payment_status);
        $this->assertEquals(0, $purchase->fresh()->total_paid);
    }

    public function test_cancel_purchase_restores_stock_and_logs_movement(): void
    {
        $purchase = Purchase::find($this->createPurchase()['purchase']['id']);
        $this->assertEquals(15, $this->stockQuantity());

        $this->postJson("/api/purchases-cancel/{$purchase->id}", ['reason' => 'Cargada por error'])->assertOk();

        $this->assertEquals(10, $this->stockQuantity());
        $fresh = $purchase->fresh();
        $this->assertSame('cancelled', $fresh->status);
        $this->assertSame('Cargada por error', $fresh->cancel_reason);
        $this->assertNotNull($fresh->cancelled_at);

        $last = Stock::where('product_id', $this->product->id)->first()->movements()->orderByDesc('id')->first();
        $this->assertSame('ajuste', $last->movement_type);
        $this->assertEquals(-5, $last->quantity);
        $this->assertEquals(15, $last->previous_quantity);
        $this->assertEquals(10, $last->new_quantity);
    }

    public function test_cancel_requires_a_reason(): void
    {
        $purchase = Purchase::find($this->createPurchase()['purchase']['id']);

        $this->postJson("/api/purchases-cancel/{$purchase->id}", ['reason' => ''])->assertStatus(422);

        $this->assertSame('received', $purchase->fresh()->status);
        $this->assertEquals(15, $this->stockQuantity());
    }

    public function test_cancel_blocked_when_purchase_has_active_payments(): void
    {
        $purchase = Purchase::find($this->createPurchase()['purchase']['id']);
        $this->postJson('/api/supplier-payments', $this->payload($purchase, 100))->assertStatus(201);

        $this->postJson("/api/purchases-cancel/{$purchase->id}", ['reason' => 'x'])->assertStatus(422);

        $this->assertSame('received', $purchase->fresh()->status);
        $this->assertEquals(15, $this->stockQuantity());
    }

    public function test_cannot_cancel_twice(): void
    {
        $purchase = Purchase::find($this->createPurchase()['purchase']['id']);
        $this->postJson("/api/purchases-cancel/{$purchase->id}", ['reason' => 'x'])->assertOk();
        $this->postJson("/api/purchases-cancel/{$purchase->id}", ['reason' => 'x'])->assertStatus(422);

        $this->assertEquals(10, $this->stockQuantity());
    }

    public function test_cancelled_purchase_rejects_new_payments(): void
    {
        $purchase = Purchase::find($this->createPurchase()['purchase']['id']);
        $this->postJson("/api/purchases-cancel/{$purchase->id}", ['reason' => 'x'])->assertOk();

        $this->postJson('/api/supplier-payments', $this->payload($purchase, 50))->assertStatus(422);

        $this->assertSame(0, $purchase->payments()->count());
    }

    // ---------------------------------------------------------------- Listado y detalle

    public function test_list_filters_by_supplier_and_payment_status(): void
    {
        $other = Supplier::create(['name' => 'Otro Proveedor']);
        $a = $this->createPurchase();
        $this->createPurchase(['supplier_id' => $other->id]);
        $this->postJson('/api/supplier-payments', $this->payload($a['purchase']['id'], 600))->assertStatus(201);

        $bySupplier = $this->getJson('/api/purchases?supplier_id=' . $other->id)->assertOk()->json('data');
        $this->assertCount(1, $bySupplier);

        $paid = $this->getJson('/api/purchases?payment_status=paid')->assertOk()->json('data');
        $this->assertCount(1, $paid);
        $this->assertSame($a['purchase']['id'], $paid[0]['id']);

        $this->assertCount(2, $this->getJson('/api/purchases')->json('data'));
        $this->assertCount(0, $this->getJson('/api/purchases?date_from=2030-01-01')->json('data'));
    }

    public function test_show_returns_items_payments_and_supplier(): void
    {
        $purchase = $this->createPurchase()['purchase'];
        $this->postJson('/api/supplier-payments', $this->payload($purchase['id'], 100))->assertStatus(201);

        $data = $this->getJson("/api/purchases/{$purchase['id']}")->assertOk()->json('data');

        $this->assertSame('Distribuidora Sur', $data['supplier']['name']);
        $this->assertCount(1, $data['items']);
        $this->assertSame('Coca 1.5', $data['items'][0]['product']['name']);
        $this->assertCount(1, $data['payments']);
        $this->assertEquals(500, $data['balance']);
    }

    public function test_other_tenant_cannot_see_or_pay_purchase(): void
    {
        $purchase = $this->createPurchase()['purchase'];

        Sanctum::actingAs($this->otherUser);

        $this->assertCount(0, $this->getJson('/api/purchases')->json('data'));
        $this->getJson("/api/purchases/{$purchase['id']}")->assertStatus(404);
        $this->postJson('/api/supplier-payments', $this->payload($purchase['id'], 10))->assertStatus(422);
        $this->postJson("/api/purchases-cancel/{$purchase['id']}", ['reason' => 'x'])->assertStatus(422);

        $this->assertSame('received', Purchase::withoutGlobalScopes()->find($purchase['id'])->status);
    }

    // ---------------------------------------------------------------- Revisión final

    public function test_superadmin_cannot_mutate_records_of_another_tenant(): void
    {
        $withoutPayments = Purchase::find($this->createPurchase()['purchase']['id']);
        $withPayment = Purchase::find($this->createPurchase()['purchase']['id']);
        $this->postJson('/api/supplier-payments', $this->payload($withPayment, 100))->assertStatus(201);
        $paymentId = $withPayment->payments()->first()->id;
        $stockBefore = $this->stockQuantity();
        $tenantA = $this->user->tenant_id;

        $superadmin = User::create(['name' => 'Root', 'email' => 'root@test.local', 'password' => bcrypt('x'), 'tenant_id' => 1, 'active' => 1]);
        Sanctum::actingAs($superadmin);

        $this->postJson("/api/purchases-cancel/{$withoutPayments->id}", ['reason' => 'x'])->assertStatus(422);
        $this->deleteJson("/api/supplier-payments/{$paymentId}")->assertStatus(404);
        $this->postJson("/api/suppliers/{$this->supplier->id}", ['_method' => 'PUT', 'data' => ['active' => 0]])->assertStatus(404);
        $this->deleteJson("/api/suppliers/{$this->supplier->id}")->assertStatus(404);
        $this->getJson("/api/suppliers/{$this->supplier->id}/statement")->assertStatus(404);

        $this->assertSame('received', Purchase::withoutGlobalScopes()->find($withoutPayments->id)->status);
        $this->assertSame($tenantA, Purchase::withoutGlobalScopes()->find($withoutPayments->id)->tenant_id);
        $this->assertSame($tenantA, Purchase::withoutGlobalScopes()->find($withPayment->id)->tenant_id);
        $this->assertNotNull(\App\Models\SupplierPayment::withoutGlobalScopes()->find($paymentId));
        $this->assertEquals(1, Supplier::withoutGlobalScopes()->find($this->supplier->id)->active);
        $this->assertSame(0, Stock::withoutGlobalScopes()->where('tenant_id', 1)->count());

        Sanctum::actingAs($this->user);
        $this->assertEquals($stockBefore, $this->stockQuantity());
    }

    public function test_business_error_messages_survive_non_debug_mode(): void
    {
        config(['app.debug' => false]);
        $purchase = Purchase::find($this->createPurchase()['purchase']['id']);

        $message = $this->postJson('/api/supplier-payments', $this->payload($purchase, 9999))
            ->assertStatus(422)->json('message');

        $this->assertStringContainsString('supera el saldo', $message);
        $this->assertStringContainsString(
            'motivo',
            $this->postJson("/api/purchases-cancel/{$purchase->id}", ['reason' => ''])->json('message')
        );
        $this->assertStringContainsString(
            'nombre',
            $this->postJson('/api/suppliers', ['name' => ''])->json('message')
        );
    }

    public function test_purchase_reuses_the_existing_stock_row_instead_of_creating_one_per_warehouse(): void
    {
        $otherWarehouse = Warehouse::create(['name' => 'Sucursal', 'active' => true]);

        $this->createPurchase(['warehouse_id' => $otherWarehouse->id]);

        $rows = Stock::where('product_id', $this->product->id)->get();
        $this->assertCount(1, $rows);
        $this->assertEquals(15, $rows->first()->quantity);
    }

    public function test_cancelling_a_purchase_into_another_warehouse_restores_the_same_stock_row(): void
    {
        $otherWarehouse = Warehouse::create(['name' => 'Sucursal', 'active' => true]);
        $purchase = Purchase::find($this->createPurchase(['warehouse_id' => $otherWarehouse->id])['purchase']['id']);

        $this->postJson("/api/purchases-cancel/{$purchase->id}", ['reason' => 'x'])->assertOk();

        $rows = Stock::where('product_id', $this->product->id)->get();
        $this->assertCount(1, $rows);
        $this->assertEquals(10, $rows->first()->quantity);
    }

    public function test_zero_cost_line_does_not_overwrite_product_cost(): void
    {
        $this->createPurchase([
            'update_cost' => true,
            'items' => [['product_id' => $this->product->id, 'quantity' => 2, 'unit_cost' => 0]],
        ]);

        $this->assertEquals(100, $this->product->fresh()->purchase_price);
        $this->assertEquals(12, $this->stockQuantity());
    }
}
