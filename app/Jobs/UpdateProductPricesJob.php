<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\PriceList;
use App\Models\ProductImportLog;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateProductPricesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $updates;
    protected $importLogId;

    /**
     * Create a new job instance.
     *
     * @param array $updates
     * @param int $importLogId
     */
    public function __construct(array $updates, int $importLogId)
    {
        $this->updates = $updates;
        $this->importLogId = $importLogId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $log = ProductImportLog::withoutGlobalScopes()->find($this->importLogId);
        if (!$log) {
            Log::error("Import log ID {$this->importLogId} not found.");
            return;
        }

        $log->update(['status' => 'processing', 'total_rows' => count($this->updates)]);
        Log::info("🚀 Starting async price update for import #{$this->importLogId}");

        $processedCount = 0;
        foreach ($this->updates as $update) {
            try {
                DB::beginTransaction();

                $product = Product::find($update['id']);
                if (!$product) {
                    Log::warning("Product ID {$update['id']} not found during async update.");
                    DB::rollBack();
                    $processedCount++;
                    continue;
                }

                // Update purchase price if changed
                if (isset($update['new_purchase_price']) && $product->purchase_price != $update['new_purchase_price']) {
                    $product->purchase_price = $update['new_purchase_price'];
                    $product->save();
                }

                // Update stock if changed
                if (isset($update['new_stock'])) {
                    $newStock = $update['old_stock']+ $update['new_stock'];
                    $delta = $update['new_stock'];
                    
                    // Encontrar un stock de almacén existente para el producto
                    $stock = Stock::where('product_id', $product->id)
                                    ->where('tenant_id', $log->tenant_id)
                                    ->first();
                                    
                    if (!$stock) {
                        // Si no tiene stock en ningún almacén, buscar el primer almacén del tenant
                        $warehouse = Warehouse::where('tenant_id', $log->tenant_id)->first();
                        if ($warehouse) {
                            $stock = new Stock();
                            $stock->product_id = $product->id;
                            $stock->warehouse_id = $warehouse->id;
                            $stock->tenant_id = $log->tenant_id;
                            $stock->quantity = 0;
                            // Se guardará abajo
                        }
                    }
                    
                    if ($stock) {
                        $movementType = $stock->exists ? 'ajuste' : 'inicial';
                        $previousQuantity = $stock->exists ? $stock->quantity : 0;
                        
                        $stock->quantity += $delta;
                        $stock->save();
                        
                        // Registrar el movimiento manualmente porque estamos en un Job de sistema y Auth no funciona
                        $movement = new StockMovement();
                        $movement->stock_id = $stock->id;
                        $movement->movement_type = $movementType;
                        $movement->quantity = $delta;
                        $movement->previous_quantity = $previousQuantity;
                        $movement->new_quantity = $stock->quantity;
                        $movement->notes = "Actualización via Importación Excel (#{$log->id})";
                        $movement->tenant_id = $log->tenant_id;
                        $movement->user_id = $log->user_id;
                        $movement->save();
                    }
                }

                // Update price lists (sale prices)
                if (isset($update['price_lists'])) {
                    foreach ($update['price_lists'] as $pl) {
                        $priceList = PriceList::where('name', $pl['name'])->first();
                        if ($priceList) {
                            $product->priceLists()->syncWithoutDetaching([
                                $priceList->id => [
                                    'sale_price' => $pl['new_sale_price'],
                                    'tenant_id' => $log->tenant_id,
                                    'created_by' => $log->user_id
                                ]
                            ]);
                        }
                    }
                }

                DB::commit();
                $processedCount++;
                
                // Update progress every 10 rows to avoid too many DB writes
                if ($processedCount % 10 == 0) {
                    $log->update(['processed_rows' => $processedCount]);
                }

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Error updating prices for product ID {$update['id']}: " . $e->getMessage());
                $processedCount++;
            }
        }

        $log->update([
            'status' => 'completed',
            'processed_rows' => $processedCount
        ]);

        Log::info("✅ Finished async price update for import #{$this->importLogId}");
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $log = ProductImportLog::withoutGlobalScopes()->find($this->importLogId);
        if ($log) {
            $log->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage()
            ]);
        }
    }
}
