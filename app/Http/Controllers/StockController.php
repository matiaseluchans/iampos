<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\StockRepository;

class StockController extends Controller
{
    private $repository;


    public function __construct(StockRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        // Filtrado adicional para stock
        if ($request->has('product_id')) {
            return $this->repository->getByProduct($request->product_id);
        }

        if ($request->has('warehouse_id')) {
            return $this->repository->getByWarehouse($request->warehouse_id);
        }

        if ($request->has('low_stock')) {
            return $this->repository->getLowStock();
        }

        return $this->repository->all();
    }

    public function show($id)
    {
        return $this->repository->get($id);
    }

    public function store(Request $request)
    {
        return $this->repository->save($request);
    }

    public function update(Request $request, $id)
    {
        return $this->repository->update($request, $id);
    }

    public function destroy($id)
    {
        return $this->repository->delete($id);
    }

    public function changestatus(Request $request, $id)
    {
        return $this->repository->changestatus($request, $id);
    }

    // Métodos específicos para stock
    public function recordMovement(Request $request, $stockId)
    {
        return $this->repository->recordMovement(
            $stockId,
            $request->movement_type,
            $request->quantity,
            $request->notes,
            //$request->source_document_type,
            //$request->source_document_id
        );
    }

    public function getMovements($stockId)
    {
        return $this->repository->getMovements($stockId);
    }


    /**
     * Crear o actualizar stock
     */
    public function createOrUpdate(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric|min:0',
            'minimum_stock' => 'nullable|numeric|min:0',
            'maximum_stock' => 'nullable|numeric|min:0'
        ]);

        return $this->repository->createOrUpdateStock(
            $request->product_id,
            $request->warehouse_id,
            $request->quantity,
            $request->minimum_stock ?? 0,
            $request->maximum_stock ?? 0
        );
    }

    /**
     * Transferir stock entre almacenes
     */
    public function transfer(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string'
        ]);

        return $this->repository->transferStock(
            $request->product_id,
            $request->from_warehouse_id,
            $request->to_warehouse_id,
            $request->quantity,
            $request->notes
        );
    }

    /**
     * Reservar stock
     */
    public function reserve(Request $request, $stockId)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:0.01'
        ]);

        return $this->repository->reserveStock($stockId, $request->quantity);
    }

    /**
     * Obtener o crear stock
     */
    public function getOrCreate(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id'
        ]);

        return $this->repository->getOrCreateStock(
            $request->product_id,
            $request->warehouse_id
        );
    }

    /**
     * Liberar reserva de stock
     */
    public function releaseReservation(Request $request, $stockId)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:0.01'
        ]);

        return $this->repository->releaseReservation($stockId, $request->quantity);
    }

    /**
     * Obtener stock con stock bajo
     */
    public function lowStock()
    {
        return $this->repository->getLowStock();
    }

    /**
     * Obtener stock por producto
     */
    public function byProduct($productId)
    {
        return $this->repository->getByProduct($productId);
    }

    /**
     * Obtener stock por almacén
     */
    public function byWarehouse($warehouseId)
    {
        return $this->repository->getByWarehouse($warehouseId);
    }

    /**
     * Resumen general de stock
     */
    public function summary()
    {
        return $this->repository->getStockSummary();
    }

    /**
     * Reporte de movimientos
     */
    public function movementsReport(Request $request)
    {
        return $this->repository->getMovementsReport($request->all());
    }

    /**
     * Reporte de valorización
     */
    public function valuationReport(Request $request)
    {
        return $this->repository->getValuationReport($request->all());
    }
}
