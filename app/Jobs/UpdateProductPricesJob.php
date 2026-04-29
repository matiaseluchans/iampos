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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateProductPricesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 900; // 15 minutes max
    public $tries = 1;

    protected $cacheKey;
    protected $importLogId;

    /**
     * Constructor receives cache key instead of full data array
     * to avoid serialization/memory issues with large payloads.
     */
    public function __construct(string $cacheKey, int $importLogId)
    {
        $this->cacheKey = $cacheKey;
        $this->importLogId = $importLogId;
    }

    /**
     * Execute the job with chunked processing and pre-loaded lookups.
     */
    public function handle(): void
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $log = ProductImportLog::withoutGlobalScopes()->find($this->importLogId);
        if (!$log) {
            Log::error("Import log ID {$this->importLogId} not found.");
            return;
        }

        // Read updates from cache instead of constructor property
        $updates = Cache::get($this->cacheKey);
        if (!$updates || empty($updates)) {
            $log->update([
                'status' => 'failed',
                'error_message' => 'Los datos de preview expiraron o no existen en caché.'
            ]);
            return;
        }

        $log->update(['status' => 'processing', 'total_rows' => count($updates)]);
        Log::info("🚀 Starting optimized price update for import #{$this->importLogId} (" . count($updates) . " products)");

        // Pre-load all price lists keyed by name (eliminates N+1)
        $allPriceLists = PriceList::withoutGlobalScopes()->get()->keyBy('name');

        $processedCount = 0;
        $errorCount = 0;
        $chunks = array_chunk($updates, 100);

        foreach ($chunks as $chunkIndex => $chunk) {
            try {
                DB::beginTransaction();

                // Batch-load all products in this chunk (1 query instead of 100)
                $productIds = array_column($chunk, 'id');
                $products = Product::withoutGlobalScopes()
                    ->with('stocks')
                    ->whereIn('id', $productIds)
                    ->get()
                    ->keyBy('id');

                foreach ($chunk as $update) {
                    $product = $products->get($update['id']);
                    if (!$product) {
                        $processedCount++;
                        continue;
                    }

                    // Update purchase price if changed
                    if (isset($update['new_purchase_price']) && $product->purchase_price != $update['new_purchase_price']) {
                        $product->purchase_price = $update['new_purchase_price'];
                        $product->save();
                    }

                    // Update stock if provided
                    if (isset($update['new_stock']) && $update['new_stock'] !== null) {
                        $this->updateStock($product, $update, $log);
                    }

                    // Update price lists using pre-loaded lookup
                    if (isset($update['price_lists']) && !empty($update['price_lists'])) {
                        $syncData = [];
                        foreach ($update['price_lists'] as $pl) {
                            $priceList = $allPriceLists->get($pl['name']);
                            if ($priceList && $pl['new_sale_price'] !== null && $pl['new_sale_price'] !== '') {
                                $syncData[$priceList->id] = [
                                    'sale_price' => (float) $pl['new_sale_price'],
                                    'tenant_id' => $log->tenant_id,
                                    'created_by' => $log->user_id
                                ];
                            }
                        }
                        if (!empty($syncData)) {
                            $product->priceLists()->syncWithoutDetaching($syncData);
                        }
                    }

                    $processedCount++;
                }

                DB::commit();

                // Update progress after each chunk
                $log->update(['processed_rows' => $processedCount]);

            } catch (\Exception $e) {
                DB::rollBack();
                $errorCount++;
                Log::error("❌ Chunk " . ($chunkIndex + 1) . " error for import #{$this->importLogId}: " . $e->getMessage());
                $processedCount += count($chunk);
            }
        }

        // Clean up cache
        Cache::forget($this->cacheKey);

        $status = ($errorCount === 0) ? 'completed' : (($errorCount < count($chunks)) ? 'completed' : 'failed');
        $errorMsg = $errorCount > 0 ? "Se produjeron errores en {$errorCount} de " . count($chunks) . " lotes." : null;

        $log->update([
            'status' => $status,
            'processed_rows' => $processedCount,
            'error_message' => $errorMsg,
        ]);

        Log::info("✅ Finished import #{$this->importLogId}: {$processedCount} processed, {$errorCount} chunk errors");
    }

    /**
     * Handle stock update for a single product.
     */
    private function updateStock(Product $product, array $update, ProductImportLog $log): void
    {
        $delta = $update['new_stock'];

        $stock = Stock::where('product_id', $product->id)
            ->where('tenant_id', $log->tenant_id)
            ->first();

        if (!$stock) {
            $warehouse = Warehouse::where('tenant_id', $log->tenant_id)->first();
            if ($warehouse) {
                $stock = new Stock();
                $stock->product_id = $product->id;
                $stock->warehouse_id = $warehouse->id;
                $stock->tenant_id = $log->tenant_id;
                $stock->quantity = 0;
            }
        }

        if ($stock) {
            $movementType = $stock->exists ? 'ajuste' : 'inicial';
            $previousQuantity = $stock->exists ? $stock->quantity : 0;

            $stock->quantity += $delta;
            $stock->save();

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

        Cache::forget($this->cacheKey);
    }
}
