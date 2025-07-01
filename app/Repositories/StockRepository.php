<?php

namespace App\Repositories;

use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StockRepository extends BaseRepository
{


    public function __construct(Stock $m, array $relations = ['product', 'warehouse'])
    {
        parent::__construct($m, $relations);
    }

    // Métodos específicos para Stock
    public function getByProduct($productId)
    {
        try {
            $query = $this->model->with($this->relations)
                ->where('product_id', $productId);

            return $this->successResponse($query->get());
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    public function getByWarehouse($warehouseId)
    {
        try {
            $query = $this->model->with($this->relations)
                ->where('warehouse_id', $warehouseId);

            return $this->successResponse($query->get());
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    public function getLowStock()
    {
        try {
            $query = $this->model->with($this->relations)
                ->whereRaw('quantity - reserved_quantity < minimum_stock');

            return $this->successResponse($query->get());
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }
    /*
    public function recordMovement($stockId, $type, $quantity, $notes = null, $sourceDocType = null, $sourceDocId = null)
    {
        DB::beginTransaction();
        try {
            $stock = $this->model->findOrFail($stockId);

            // Actualizar stock
            $stock->quantity += $quantity;
            $stock->save();

            // Registrar movimiento
            $movement = $stock->movements()->create([
                'movement_type' => $type,
                'quantity' => $quantity,
                'previous_quantity' => $stock->quantity - $quantity,
                'new_quantity' => $stock->quantity,
                //'source_document_type' => $sourceDocType,
                //'source_document_id' => $sourceDocId,
                'notes' => $notes,
                'tenant_id' => $stock->tenant_id,
                'user_id' => auth()->id()
            ]);

            DB::commit();
            return $this->successResponse($movement);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return $this->errorResponse($e);
        }
    }
*/
    public function getMovements($stockId)
    {
        try {
            $stock = $this->model->findOrFail($stockId);
            $movements = $stock->movements()
                ->with(['user' /*, 'sourceDocument'*/])
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->successResponse($movements);
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }


    /**
     * Crear o actualizar stock para un producto
     */
    public function createOrUpdateStock($productId, $warehouseId, $quantity, $minimumStock = 0, $maximumStock = 0)
    {
        try {
            $stock = Stock::createOrUpdate(
                $productId,
                $warehouseId,
                $quantity,
                auth()->user()->tenant_id
            );

            if ($minimumStock > 0) {
                $stock->minimum_stock = $minimumStock;
            }
            if ($maximumStock > 0) {
                $stock->maximum_stock = $maximumStock;
            }
            $stock->save();

            return $this->successResponse($stock);
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    /**
     * Registrar movimiento de stock con validaciones
     */
    public function recordMovement($stockId, $type, $quantity, $notes = null, $sourceDocType = null, $sourceDocId = null)
    {
        // Validaciones
        $validator = Validator::make([
            'movement_type' => $type,
            'quantity' => $quantity
        ], [
            'movement_type' => 'required|in:entrada,salida,ajuste,transferencia',
            'quantity' => 'required|numeric|not_in:0'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        DB::beginTransaction();
        try {
            $stock = $this->model->findOrFail($stockId);
            $previousQuantity = $stock->quantity;

            // Validar stock suficiente para salidas
            if (in_array($type, ['salida', 'transferencia']) && $quantity > 0) {
                $quantity = -abs($quantity); // Asegurar que sea negativo
                if ($stock->available < abs($quantity)) {
                    throw new \Exception('Stock insuficiente. Disponible: ' . $stock->available);
                }
            }

            // Para entradas, asegurar que sea positivo
            if ($type === 'entrada' && $quantity < 0) {
                $quantity = abs($quantity);
            }

            // Actualizar stock
            $stock->quantity += $quantity;
            $stock->save();

            // Registrar movimiento
            $movement = $stock->movements()->create([
                'movement_type' => $type,
                'quantity' => $quantity,
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $stock->quantity,
                'source_document_type' => $sourceDocType,
                'source_document_id' => $sourceDocId,
                'notes' => $notes,
                'tenant_id' => $stock->tenant_id,
                'user_id' => auth()->id()
            ]);

            DB::commit();
            return $this->successResponse($movement->load('stock.product'));
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return $this->errorResponse($e);
        }
    }

    /**
     * Transferir stock entre almacenes
     */
    public function transferStock($productId, $fromWarehouseId, $toWarehouseId, $quantity, $notes = null)
    {
        DB::beginTransaction();
        try {
            $tenantId = auth()->user()->tenant_id;

            // Stock origen
            $fromStock = $this->model->where([
                'product_id' => $productId,
                'warehouse_id' => $fromWarehouseId,
                'tenant_id' => $tenantId
            ])->first();

            if (!$fromStock || $fromStock->available < $quantity) {
                throw new \Exception('Stock insuficiente en almacén origen');
            }

            // Stock destino (crear si no existe)
            $toStock = $this->model->firstOrCreate([
                'product_id' => $productId,
                'warehouse_id' => $toWarehouseId,
                'tenant_id' => $tenantId
            ], [
                'quantity' => 0,
                'reserved_quantity' => 0,
                'created_by' => auth()->id()
            ]);

            // Registrar salida del almacén origen
            $this->recordMovement($fromStock->id, 'transferencia', -$quantity, $notes);

            // Registrar entrada al almacén destino
            $this->recordMovement($toStock->id, 'transferencia', $quantity, $notes);

            DB::commit();
            return $this->successResponse([
                'from_stock' => $fromStock->fresh(),
                'to_stock' => $toStock->fresh()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return $this->errorResponse($e);
        }
    }

    /**
     * Obtener o crear stock para un producto
     */
    public function getOrCreateStock($productId, $warehouseId)
    {
        try {
            $stock = $this->model->firstOrCreate([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'tenant_id' => auth()->user()->tenant_id
            ], [
                'quantity' => 0,
                'reserved_quantity' => 0,
                'minimum_stock' => 0,
                'created_by' => auth()->id()
            ]);

            return $this->successResponse($stock->load($this->relations));
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    /**
     * Reservar stock
     */
    public function reserveStock($stockId, $quantity)
    {
        DB::beginTransaction();
        try {
            $stock = $this->model->findOrFail($stockId);
            $stock->reserve($quantity);

            DB::commit();
            return $this->successResponse($stock);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return $this->errorResponse($e);
        }
    }

    /**
     * Liberar reserva de stock
     */
    public function releaseReservation($stockId, $quantity)
    {
        DB::beginTransaction();
        try {
            $stock = $this->model->findOrFail($stockId);
            $stock->releaseReservation($quantity);

            DB::commit();
            return $this->successResponse($stock);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return $this->errorResponse($e);
        }
    }

    /**
     * Resumen general de stock
     */
    public function getStockSummary()
    {
        try {


            $summary = [
                'total_products' => $this->model->distinct('product_id')->count(),
                'total_stock_value' => $this->model->with('product')
                    ->get()
                    ->sum(function ($stock) {
                        return $stock->quantity * ($stock->product->purchase_price ?? 0);
                    }),
                'low_stock_count' => $this->model->whereRaw('quantity - reserved_quantity < minimum_stock')->count(),
                'out_of_stock_count' => $this->model->where('quantity', '<=', 0)->count(),
            ];


            return $this->successResponse($summary);
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    /**
     * Reporte de movimientos
     */
    public function getMovementsReport($filters = [])
    {
        try {
            $query = StockMovement::with(['stock.product', 'user']);

            if (isset($filters['date_from'])) {
                $query->whereDate('created_at', '>=', $filters['date_from']);
            }

            if (isset($filters['date_to'])) {
                $query->whereDate('created_at', '<=', $filters['date_to']);
            }

            if (isset($filters['movement_type'])) {
                $query->where('movement_type', $filters['movement_type']);
            }

            if (isset($filters['product_id'])) {
                $query->whereHas('stock', function ($q) use ($filters) {
                    $q->where('product_id', $filters['product_id']);
                });
            }

            $movements = $query->orderBy('created_at', 'desc')->paginate(50);

            return $this->successResponse($movements);
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }
}
