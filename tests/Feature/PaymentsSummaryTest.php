<?php

namespace Tests\Feature;

use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Resumen de pagos unificado: ingresos (payments de ventas) y egresos (supplier_payments de compras).
 * Corre contra la BD "testing" y revierte cada test.
 */
class PaymentsSummaryTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;
    private User $otherUser;
    private int $tenantA;
    private int $tenantB;
    private PaymentMethod $cash;
    private PaymentMethod $transfer;
    private int $seq = 0;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::connection()->getDatabaseName() !== 'testing') {
            $this->fail('Estos tests solo pueden correr contra la BD "testing".');
        }

        if (!DB::table('tenants')->where('id', 1)->exists()) {
            DB::table('tenants')->insert(['id' => 1, 'slug' => 'root', 'name' => 'Root', 'created_at' => now(), 'updated_at' => now()]);
        }
        $this->tenantA = DB::table('tenants')->insertGetId(['slug' => 'a', 'name' => 'A', 'created_at' => now(), 'updated_at' => now()]);
        $this->tenantB = DB::table('tenants')->insertGetId(['slug' => 'b', 'name' => 'B', 'created_at' => now(), 'updated_at' => now()]);

        $this->user = User::create(['name' => 'User A', 'email' => 'a@test.local', 'password' => bcrypt('x'), 'tenant_id' => $this->tenantA, 'active' => 1]);
        $this->otherUser = User::create(['name' => 'User B', 'email' => 'b@test.local', 'password' => bcrypt('x'), 'tenant_id' => $this->tenantB, 'active' => 1]);

        Sanctum::actingAs($this->user);

        $this->cash = PaymentMethod::create(['name' => 'Efectivo', 'active' => 1]);
        $this->transfer = PaymentMethod::create(['name' => 'Transferencia', 'active' => 1]);
    }

    // ---------------------------------------------------------------- helpers

    private function methodFor(int $tenantId): int
    {
        return DB::table('payment_methods')->insertGetId([
            'name' => 'Efectivo ' . $tenantId, 'active' => 1, 'tenant_id' => $tenantId,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    /** Pago de una venta (tabla payments) con cliente y orden propios. */
    private function salePayment(float $amount, string $datetime, ?PaymentMethod $method = null, ?int $tenantId = null): int
    {
        $tenantId ??= $this->tenantA;
        $methodId = $method?->id ?? ($tenantId === $this->tenantA ? $this->cash->id : $this->methodFor($tenantId));
        $n = ++$this->seq;

        $customerId = DB::table('customers')->insertGetId([
            'address' => "Calle {$n}", 'firstname' => "Cliente{$n}", 'telephone' => "11-0000-{$n}",
            'tenant_id' => $tenantId, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $orderId = DB::table('orders')->insertGetId([
            'order_date' => $datetime, 'order_number' => str_pad((string) $n, 10, '0', STR_PAD_LEFT),
            'customer_id' => $customerId, 'tenant_id' => $tenantId, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return DB::table('payments')->insertGetId([
            'order_id' => $orderId, 'payment_method_id' => $methodId, 'amount' => $amount,
            'payment_date' => $datetime, 'tenant_id' => $tenantId, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    /** Pago a proveedor (tabla supplier_payments) con proveedor y compra propios. */
    private function purchasePayment(float $amount, string $datetime, ?PaymentMethod $method = null, ?int $tenantId = null, bool $voided = false): int
    {
        $tenantId ??= $this->tenantA;
        $methodId = $method?->id ?? ($tenantId === $this->tenantA ? $this->cash->id : $this->methodFor($tenantId));
        $n = ++$this->seq;

        $supplierId = DB::table('suppliers')->insertGetId([
            'name' => "Proveedor {$n}", 'telephone' => "11-1111-{$n}", 'tenant_id' => $tenantId,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $purchaseId = DB::table('purchases')->insertGetId([
            'purchase_date' => $datetime, 'purchase_number' => str_pad((string) $n, 10, '0', STR_PAD_LEFT),
            'supplier_id' => $supplierId, 'total_amount' => 5000, 'payment_status' => 'partial',
            'tenant_id' => $tenantId, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return DB::table('supplier_payments')->insertGetId([
            'purchase_id' => $purchaseId, 'payment_method_id' => $methodId, 'amount' => $amount,
            'payment_date' => $datetime, 'tenant_id' => $tenantId, 'created_at' => now(), 'updated_at' => now(),
            'deleted_at' => $voided ? now() : null,
        ]);
    }

    private function service(): PaymentService
    {
        return app(PaymentService::class);
    }

    private function summary(array $filters = []): \Illuminate\Support\Collection
    {
        return $this->service()->getUnifiedSummary(array_merge(
            ['start_date' => '2026-11-01', 'end_date' => '2026-11-30'],
            $filters
        ));
    }

    // ---------------------------------------------------------------- servicio: resumen

    public function test_summary_combines_income_and_expense_for_same_day_and_method(): void
    {
        $this->salePayment(1000, '2026-11-10 10:00:00');
        $this->purchasePayment(400, '2026-11-10 15:00:00');

        $rows = $this->summary();

        $this->assertCount(1, $rows);
        $row = $rows->first();
        $this->assertSame('2026-11-10', $row['date']);
        $this->assertSame($this->cash->id, $row['payment_method_id']);
        $this->assertSame('Efectivo', $row['payment_method_name']);
        $this->assertEquals(1000, $row['income']);
        $this->assertEquals(-400, $row['expense']);
        $this->assertEquals(600, $row['net']);
        $this->assertSame(2, $row['payment_count']);
    }

    public function test_summary_keeps_refunds_as_negative_income(): void
    {
        $this->salePayment(1000, '2026-11-10 10:00:00');
        $this->salePayment(-200, '2026-11-10 11:00:00');

        $row = $this->summary()->first();

        $this->assertEquals(800, $row['income']);
        $this->assertEquals(0, $row['expense']);
        $this->assertEquals(800, $row['net']);
    }

    public function test_summary_day_with_only_purchase_payments_has_zero_income(): void
    {
        $this->purchasePayment(250, '2026-11-12 09:00:00');

        $rows = $this->summary();

        $this->assertCount(1, $rows);
        $this->assertEquals(0, $rows->first()['income']);
        $this->assertEquals(-250, $rows->first()['expense']);
        $this->assertEquals(-250, $rows->first()['net']);
    }

    public function test_summary_origin_filter(): void
    {
        $this->salePayment(1000, '2026-11-10 10:00:00');
        $this->purchasePayment(400, '2026-11-10 15:00:00');

        $sales = $this->summary(['origin' => 'sales'])->first();
        $this->assertEquals(1000, $sales['income']);
        $this->assertEquals(0, $sales['expense']);
        $this->assertSame(1, $sales['payment_count']);

        $purchases = $this->summary(['origin' => 'purchases'])->first();
        $this->assertEquals(0, $purchases['income']);
        $this->assertEquals(-400, $purchases['expense']);
        $this->assertSame(1, $purchases['payment_count']);
    }

    public function test_summary_payment_method_filter_applies_to_both_sources(): void
    {
        $this->salePayment(1000, '2026-11-10 10:00:00', $this->cash);
        $this->salePayment(700, '2026-11-10 10:30:00', $this->transfer);
        $this->purchasePayment(400, '2026-11-10 15:00:00', $this->transfer);

        $rows = $this->summary(['payment_methods' => [$this->transfer->id]]);

        $this->assertCount(1, $rows);
        $this->assertSame($this->transfer->id, $rows->first()['payment_method_id']);
        $this->assertEquals(700, $rows->first()['income']);
        $this->assertEquals(-400, $rows->first()['expense']);
    }

    public function test_summary_excludes_voided_payments_and_other_tenants(): void
    {
        $this->purchasePayment(100, '2026-11-10 10:00:00');
        $this->purchasePayment(900, '2026-11-10 11:00:00', null, null, true);
        $this->purchasePayment(5000, '2026-11-10 12:00:00', null, $this->tenantB);
        $this->salePayment(7000, '2026-11-10 12:00:00', null, $this->tenantB);

        $rows = $this->summary();

        $this->assertCount(1, $rows);
        $this->assertEquals(-100, $rows->first()['expense']);
        $this->assertEquals(0, $rows->first()['income']);
    }

    public function test_summary_date_range_includes_last_second_of_end_date(): void
    {
        $this->purchasePayment(100, '2026-11-30 23:59:59');
        $this->purchasePayment(200, '2026-12-01 00:00:00');
        $this->purchasePayment(300, '2026-10-31 23:59:59');

        $rows = $this->summary();

        $this->assertCount(1, $rows);
        $this->assertSame('2026-11-30', $rows->first()['date']);
        $this->assertEquals(-100, $rows->first()['expense']);
    }

    public function test_summary_sales_only_numbers_match_legacy_query(): void
    {
        $this->salePayment(1000, '2026-11-10 10:00:00', $this->cash);
        $this->salePayment(-150, '2026-11-10 11:00:00', $this->cash);
        $this->salePayment(300, '2026-11-11 10:00:00', $this->transfer);

        $legacy = $this->service()->getPaymentsSummaryQuery([])
            ->where('payments.payment_date', '>=', '2026-11-01 00:00:00')
            ->where('payments.payment_date', '<=', '2026-11-30 23:59:59')
            ->get()
            ->mapWithKeys(fn($r) => [$r->date . '|' . $r->payment_method_id => (float) $r->amount]);

        $rows = $this->summary();

        $this->assertCount($legacy->count(), $rows);
        foreach ($rows as $row) {
            $this->assertEquals($legacy[$row['date'] . '|' . $row['payment_method_id']], $row['income']);
            $this->assertEquals(0, $row['expense']);
        }
    }

    public function test_summary_is_ordered_by_date_desc_then_absolute_net_desc(): void
    {
        $this->salePayment(100, '2026-11-10 10:00:00', $this->cash);
        $this->salePayment(900, '2026-11-10 10:00:00', $this->transfer);
        $this->salePayment(50, '2026-11-12 10:00:00', $this->cash);

        $rows = $this->summary()->values();

        $this->assertSame(['2026-11-12', '2026-11-10', '2026-11-10'], $rows->pluck('date')->all());
        $this->assertSame($this->transfer->id, $rows[1]['payment_method_id']);
    }

    // ---------------------------------------------------------------- servicio: detalle

    public function test_details_return_both_origins_with_reference_and_counterparty(): void
    {
        $this->salePayment(1000, '2026-11-10 10:00:00');
        $this->purchasePayment(400, '2026-11-10 15:00:00');

        $rows = $this->service()->getPaymentDetails('2026-11-10', $this->cash->id)->values();

        $this->assertCount(2, $rows);

        $purchase = $rows->firstWhere('origin', 'purchase');
        $this->assertSame('Proveedor 2', $purchase['counterparty_name']);
        $this->assertSame('11-1111-2', $purchase['counterparty_contact']);
        $this->assertSame('0000000002', $purchase['reference_number']);
        $this->assertEquals(-400, $purchase['amount']);
        $this->assertSame('Efectivo', $purchase['payment_method']);

        $sale = $rows->firstWhere('origin', 'sale');
        $this->assertSame('0000000001', $sale['reference_number']);
        $this->assertSame('Calle 1, Cliente1', $sale['counterparty_name']);
        $this->assertSame('11-0000-1', $sale['counterparty_contact']);
        $this->assertEquals(1000, $sale['amount']);

        // más reciente primero
        $this->assertSame('purchase', $rows->first()['origin']);
    }

    // ---------------------------------------------------------------- endpoints

    public function test_summary_endpoint_returns_income_expense_net_and_total_count(): void
    {
        $this->salePayment(1000, '2026-11-10 10:00:00', $this->cash);
        $this->purchasePayment(400, '2026-11-10 15:00:00', $this->cash);
        $this->purchasePayment(100, '2026-11-11 15:00:00', $this->transfer);

        $response = $this->getJson('/api/payments-summary?start_date=2026-11-01&end_date=2026-11-30')->assertOk();

        $response->assertJsonPath('success', true)->assertJsonPath('total', 2);
        $first = $response->json('data.1');
        $this->assertSame('2026-11-10', $first['date']);
        $this->assertEquals(1000, $first['income']);
        $this->assertEquals(-400, $first['expense']);
        $this->assertEquals(600, $first['net']);
    }

    public function test_summary_endpoint_origin_filter_and_invalid_origin_is_422(): void
    {
        $this->salePayment(1000, '2026-11-10 10:00:00');
        $this->purchasePayment(400, '2026-11-10 15:00:00');

        $this->getJson('/api/payments-summary?start_date=2026-11-01&end_date=2026-11-30&origin=purchases')
            ->assertOk()
            ->assertJsonPath('data.0.income', 0)
            ->assertJsonPath('data.0.expense', -400);

        $this->getJson('/api/payments-summary?origin=otra')
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_detail_endpoint_returns_rows_from_both_origins(): void
    {
        $this->salePayment(1000, '2026-11-10 10:00:00');
        $this->purchasePayment(400, '2026-11-10 15:00:00');

        $response = $this->getJson("/api/payments-summary/detail?date=2026-11-10&payment_method_id={$this->cash->id}")->assertOk();
        $this->assertSame(['purchase', 'sale'], collect($response->json('data'))->pluck('origin')->all());

        $only = $this->getJson("/api/payments-summary/detail?date=2026-11-10&payment_method_id={$this->cash->id}&origin=sales")->assertOk();
        $this->assertCount(1, $only->json('data'));
    }

    public function test_detail_endpoint_requires_date_and_method(): void
    {
        $this->getJson('/api/payments-summary/detail?date=2026-11-10')->assertStatus(400);
    }

    // ---------------------------------------------------------------- export

    private function exportSheets(string $origin = 'all'): array
    {
        $export = new \App\Exports\PaymentsSummaryExport('2026-11-01', '2026-11-30', null, $this->service(), $origin);

        return $export->sheets();
    }

    public function test_export_summary_sheet_has_income_expense_net_columns(): void
    {
        $this->salePayment(1000, '2026-11-10 10:00:00');
        $this->purchasePayment(400, '2026-11-10 15:00:00');

        [$summary] = $this->exportSheets();

        $this->assertSame(
            ['Fecha', 'Método de Pago', 'Ingresos', 'Egresos', 'Neto', 'Cantidad de Transacciones'],
            $summary->headings()
        );
        $rows = $summary->collection();
        $this->assertCount(1, $rows);
        $this->assertEquals(['2026-11-10', 'Efectivo', 1000, -400, 600, 2], $summary->map($rows->first()));
    }

    public function test_export_detail_sheet_includes_origin_and_both_sources(): void
    {
        $this->salePayment(1000, '2026-11-10 10:00:00');
        $this->salePayment(-100, '2026-11-10 11:00:00');
        $this->purchasePayment(400, '2026-11-10 15:00:00');
        $this->purchasePayment(999, '2026-11-10 16:00:00', null, $this->tenantB);

        [, $detail] = $this->exportSheets();

        $this->assertSame(
            ['Fecha Pago', 'Fecha Grupo', 'Método de Pago', 'Origen', 'Referencia', 'Contraparte', 'Contacto', 'Monto', 'Tipo Monto', 'Estado'],
            $detail->headings()
        );

        $mapped = $detail->collection()->map(fn($r) => $detail->map($r));
        $this->assertCount(3, $mapped);
        $this->assertEquals(['Compra'], $mapped->where(3, 'Compra')->pluck(3)->unique()->values()->all());
        $this->assertEquals(['Egreso'], $mapped->where(3, 'Compra')->pluck(8)->all());
        $this->assertEquals(['Ingreso', 'Reembolso'], $mapped->where(3, 'Venta')->pluck(8)->sort()->values()->all());
        $this->assertSame('partial', $mapped->firstWhere(3, 'Compra')[9]);
    }

    public function test_export_respects_origin_filter(): void
    {
        $this->salePayment(1000, '2026-11-10 10:00:00');
        $this->purchasePayment(400, '2026-11-10 15:00:00');

        [$summary, $detail] = $this->exportSheets('purchases');

        $this->assertEquals(0, $summary->collection()->first()['income']);
        $this->assertSame(['purchase'], $detail->collection()->pluck('origin')->unique()->values()->all());
    }

    public function test_export_renders_a_real_xlsx_file(): void
    {
        $this->salePayment(1000, '2026-11-10 10:00:00');
        $this->purchasePayment(400, '2026-11-10 15:00:00');

        $export = new \App\Exports\PaymentsSummaryExport('2026-11-01', '2026-11-30', null, $this->service());
        $binary = \Maatwebsite\Excel\Facades\Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);

        $this->assertStringStartsWith('PK', $binary); // un .xlsx es un zip
    }

    public function test_export_endpoint_downloads_xlsx(): void
    {
        $this->purchasePayment(400, '2026-11-10 15:00:00');

        $this->get('/api/payments-summary/export-excel?start_date=2026-11-01&end_date=2026-11-30&origin=purchases')
            ->assertOk()
            ->assertHeader('content-disposition');
    }

    public function test_details_origin_filter_and_other_tenants_are_excluded(): void
    {
        $this->salePayment(1000, '2026-11-10 10:00:00');
        $this->purchasePayment(400, '2026-11-10 15:00:00');
        $this->purchasePayment(999, '2026-11-10 16:00:00', null, $this->tenantB);

        $only = $this->service()->getPaymentDetails('2026-11-10', $this->cash->id, 'purchases');

        $this->assertCount(1, $only);
        $this->assertSame('purchase', $only->first()['origin']);
    }
}
