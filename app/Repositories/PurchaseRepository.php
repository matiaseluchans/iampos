<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Support\DateInput;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseRepository extends BaseRepository
{
    private const DETAIL_RELATIONS = ['supplier', 'warehouse', 'items.product', 'payments.paymentMethod'];

    public function __construct(Purchase $m)
    {
        parent::__construct($m, self::DETAIL_RELATIONS);
    }

    /**
     * Listado con filtros: supplier_id, date_from, date_to (Y-m-d), payment_status, status, purchase_number.
     */
    public function search($request)
    {
        try {
            $query = Purchase::query()->with(['supplier', 'payments.paymentMethod']);

            if ($request->filled('supplier_id')) {
                $query->where('supplier_id', $request->input('supplier_id'));
            }
            if ($request->filled('payment_status')) {
                $query->where('payment_status', $request->input('payment_status'));
            }
            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }
            if ($request->filled('purchase_number')) {
                $query->where('purchase_number', 'LIKE', '%' . $request->input('purchase_number') . '%');
            }
            if ($request->filled('date_from')) {
                $query->where('purchase_date', '>=', Carbon::parse($request->input('date_from'))->startOfDay());
            }
            if ($request->filled('date_to')) {
                $query->where('purchase_date', '<=', Carbon::parse($request->input('date_to'))->endOfDay());
            }

            return $this->successResponse($query->orderBy('purchase_date', 'desc')->orderBy('id', 'desc')->get());
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    public function get($id)
    {
        try {
            return $this->successResponse(Purchase::with(self::DETAIL_RELATIONS)->findOrFail($id));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->domainError('La compra no existe', 404);
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    public function save($request)
    {
        try {
            $user = Auth::user();
            $form = $request->all();

            $purchase = DB::transaction(function () use ($form, $user) {
                $items = $this->validatedItems($form['items'] ?? []);

                $supplier = Supplier::where('id', $form['supplier_id'] ?? 0)
                    ->where('tenant_id', $user->tenant_id)
                    ->where('active', 1)
                    ->first();
                if (!$supplier) {
                    throw new \DomainException('El proveedor no existe o está inactivo');
                }

                $warehouse = $this->resolveWarehouse($form['warehouse_id'] ?? null, $user->tenant_id);

                $this->assertProductsBelongToTenant($items, $user->tenant_id);

                $subtotal = round(collect($items)->sum('total_cost'), 2);
                $discount = round((float) ($form['discount_amount'] ?? 0), 2);
                $additional = round((float) ($form['aditional'] ?? 0), 2);
                if ($discount < 0 || $additional < 0) {
                    throw new \DomainException('El descuento y el adicional no pueden ser negativos');
                }
                if ($discount > $subtotal) {
                    throw new \DomainException('El descuento no puede ser mayor al subtotal');
                }

                $purchaseDate = DateInput::parse($form['purchase_date'] ?? null, 'fecha de compra') ?? Carbon::now();
                $dueDate = DateInput::parse($form['due_date'] ?? null, 'fecha de vencimiento');
                if (!$dueDate && $supplier->payment_term_days > 0) {
                    $dueDate = $purchaseDate->copy()->addDays($supplier->payment_term_days);
                }

                $purchase = Purchase::create([
                    'purchase_date' => $purchaseDate->format('Y-m-d H:i:s'),
                    'supplier_id' => $supplier->id,
                    'warehouse_id' => $warehouse->id,
                    'supplier_document' => $form['supplier_document'] ?? null,
                    'due_date' => $dueDate ? $dueDate->format('Y-m-d') : null,
                    'status' => Purchase::STATUS_RECEIVED,
                    'payment_status' => Purchase::PAYMENT_PENDING,
                    'quantity_products' => count($items),
                    'subtotal' => $subtotal,
                    'discount_amount' => $discount,
                    'aditional' => $additional,
                    'total_amount' => round($subtotal - $discount + $additional, 2),
                    'total_paid' => 0,
                    'notes' => $form['notes'] ?? null,
                ]);

                foreach ($items as $item) {
                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_cost' => $item['unit_cost'],
                        'total_cost' => $item['total_cost'],
                    ]);
                }

                $this->moveStock($items, $warehouse->id, 1, 'entrada', "Compra #{$purchase->purchase_number}", $user);

                if (!empty($form['update_cost'])) {
                    foreach ($items as $item) {
                        // Un costo 0 (bonificación) no debe pisar el costo real del producto
                        if ($item['unit_cost'] <= 0) {
                            continue;
                        }
                        Product::withoutGlobalScope('order')
                            ->where('id', $item['product_id'])
                            ->update(['purchase_price' => $item['unit_cost']]);
                    }
                }

                return $purchase;
            });

            return $this->successResponseCreate([
                'purchase' => $purchase->fresh(),
                'items' => $purchase->items()->get(),
            ]);
        } catch (\DomainException $e) {
            return $this->domainError($e->getMessage());
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    public function cancel($request, $id)
    {
        try {
            $user = Auth::user();
            $reason = trim((string) $request->input('reason', ''));

            DB::transaction(function () use ($id, $reason, $user) {
                if ($reason === '') {
                    throw new \DomainException('Indicá el motivo de la anulación');
                }

                $purchase = Purchase::where('id', $id)->where('tenant_id', $user->tenant_id)->lockForUpdate()->first();
                if (!$purchase) {
                    throw new \DomainException('La compra no existe');
                }
                if ($purchase->status !== Purchase::STATUS_RECEIVED) {
                    throw new \DomainException('La compra ya está anulada');
                }
                if ($purchase->payments()->lockForUpdate()->exists()) {
                    throw new \DomainException('La compra tiene pagos registrados. Anulá los pagos antes de anular la compra');
                }

                $items = $purchase->items()->get()
                    ->map(fn($i) => ['product_id' => $i->product_id, 'quantity' => (float) $i->quantity])
                    ->all();

                $this->moveStock($items, $purchase->warehouse_id, -1, 'ajuste', "Anulación compra #{$purchase->purchase_number}", $user);

                $purchase->status = Purchase::STATUS_CANCELLED;
                $purchase->cancelled_at = now();
                $purchase->cancel_reason = mb_substr($reason, 0, 500);
                $purchase->save();
            });

            return $this->successResponse(Purchase::with(self::DETAIL_RELATIONS)->find($id));
        } catch (\DomainException $e) {
            return $this->domainError($e->getMessage());
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    /**
     * @return array<int, array{product_id:int, quantity:float, unit_cost:float, total_cost:float}>
     */
    private function validatedItems($rawItems): array
    {
        if (!is_array($rawItems) || count($rawItems) === 0) {
            throw new \DomainException('La compra debe tener al menos un producto');
        }

        $items = [];
        foreach ($rawItems as $raw) {
            $quantity = $raw['quantity'] ?? null;
            $cost = $raw['unit_cost'] ?? null;

            if (empty($raw['product_id'])) {
                throw new \DomainException('Todos los ítems deben tener un producto');
            }
            if (!is_numeric($quantity) || (float) $quantity <= 0) {
                throw new \DomainException('La cantidad de cada producto debe ser mayor a 0');
            }
            if (!is_numeric($cost) || (float) $cost < 0) {
                throw new \DomainException('El costo de cada producto no puede ser negativo');
            }

            $items[] = [
                'product_id' => (int) $raw['product_id'],
                'quantity' => round((float) $quantity, 2),
                'unit_cost' => round((float) $cost, 2),
                'total_cost' => round((float) $quantity * (float) $cost, 2),
            ];
        }

        return $items;
    }

    private function assertProductsBelongToTenant(array $items, $tenantId): void
    {
        $ids = collect($items)->pluck('product_id')->unique()->values();
        $found = Product::withoutGlobalScope('order')->where('tenant_id', $tenantId)->whereIn('id', $ids)->count();
        if ($found !== $ids->count()) {
            throw new \DomainException('Alguno de los productos no existe');
        }
    }

    private function resolveWarehouse($warehouseId, $tenantId): Warehouse
    {
        $query = Warehouse::where('tenant_id', $tenantId)->where('active', true);

        if ($warehouseId) {
            $warehouse = (clone $query)->where('id', $warehouseId)->first();
            if (!$warehouse) {
                throw new \DomainException('El depósito no existe o está inactivo');
            }
            return $warehouse;
        }

        $warehouse = $query->orderBy('id')->first();
        if (!$warehouse) {
            throw new \DomainException('No hay depósitos activos para recibir la mercadería');
        }

        return $warehouse;
    }

    /**
     * Suma (direction 1) o resta (direction -1) stock, un movimiento por producto.
     * Crea la fila de stock si el producto todavía no la tiene en ese depósito.
     */
    private function moveStock(array $items, ?int $warehouseId, int $direction, string $movementType, string $notes, $user): void
    {
        $perProduct = collect($items)->groupBy('product_id')->map(fn($rows) => (float) $rows->sum('quantity'));

        foreach ($perProduct as $productId => $quantity) {
            $base = Stock::where('product_id', $productId)->where('tenant_id', $user->tenant_id);

            $stock = $warehouseId
                ? (clone $base)->where('warehouse_id', $warehouseId)->lockForUpdate()->first()
                : null;

            // Las órdenes descuentan por producto sin mirar el depósito: se mantiene una sola fila
            // por producto en vez de crear una nueva al comprar en otro depósito.
            $reused = false;
            if (!$stock) {
                $stock = (clone $base)->orderBy('id')->lockForUpdate()->first();
                $reused = (bool) $stock && $warehouseId && $stock->warehouse_id != $warehouseId;
            }

            if (!$stock) {
                $stock = Stock::create([
                    'product_id' => $productId,
                    'warehouse_id' => $warehouseId,
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                ]);
            }

            $previous = (float) $stock->quantity;
            $delta = $direction * $quantity;
            $stock->quantity = $previous + $delta;
            $stock->save();

            $stock->movements()->create([
                'movement_type' => $movementType,
                'quantity' => $delta,
                'previous_quantity' => $previous,
                'new_quantity' => $previous + $delta,
                'notes' => $reused ? $notes . ' (se usó la fila de stock existente del producto)' : $notes,
                'tenant_id' => $user->tenant_id,
                'user_id' => $user->id,
            ]);
        }
    }
}
