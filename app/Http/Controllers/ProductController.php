<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\ProductImportLog;
use App\Models\PriceList;
use App\Exports\ProductsListAndStockExport;
use App\Imports\ProductImport;
use App\Jobs\UpdateProductPricesJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class ProductController extends ApiController
{
    protected $model;
    private $relations;


    public function __construct(array $relations = ['brand:id,name', 'category:id,name', 'priceLists:id,name,is_default'])
    {
        $this->model = new Product();
        $this->relations = $relations;
    }

    public function index()
    {
        try {

            $tenantId = Auth::user()->tenant_id;

            $cacheKey = "tenant_{$tenantId}_products";



            return Cache::remember($cacheKey, 60 * 24, function () {

                $query = $this->model->with($this->relations)
                    ->withSum('stocks as total_stock', 'quantity')
                    ->withSum('stocks as total_reserved', 'reserved_quantity');

                return $this->successResponse($query->get());
            });
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    public function show($id)
    {
        try {
            return $this->successResponse(
                $this->model->with($this->relations)->findOrFail($id)
            );
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            /* $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255',
                'productos_categorias_id' => 'nullable|exists:productos_categorias,id',
                'marca_id' => 'nullable|exists:marcas,id',
                'codigo' => 'nullable|string|max:50|unique:productos,codigo',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4048',
                'precio_compra' => 'nullable|numeric|min:0',
                'precio_venta' => 'nullable|numeric|min:0',
                'activo' => 'boolean'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors(), 422);
            }
            */
            $data = $request->except('image');

            if ($request->hasFile('image')) {
                $data['image'] = $this->uploadImage($request->file('image'));
            }

            //$data['activo'] = filter_var($request->input('activo', true), FILTER_VALIDATE_BOOLEAN);

            $p = $this->model->create($data);



            $this->syncPriceLists($p, $request);

            DB::commit();

            $tenantId = Auth::user()->tenant_id;

            $cacheKey = "tenant_{$tenantId}_products";

            Cache::forget($cacheKey);

            return $this->successResponseCreate($p->load($this->relations));
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return $this->errorResponse($e->getMessage());
        }
    }


    private function syncPriceLists($product, Request $request)
    {
        if (!$request->has('price_lists')) {
            return;
        }

        $priceListsData = $request->input('price_lists');

        // Debug: Verificar qué tipo de datos llegan
        \Log::info('Price lists data type: ' . gettype($priceListsData));
        \Log::info('Price lists data: ', (array) $priceListsData);

        // Si es string, intentar decodificar como JSON
        if (is_string($priceListsData)) {
            $decoded = json_decode($priceListsData, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $priceListsData = $decoded;
            }
        }

        $syncData = [];

        foreach ($priceListsData as $priceListId => $salePrice) {
            // Validar que el priceListId sea numérico y el precio sea válido
            if (is_numeric($priceListId) && $salePrice !== null && $salePrice !== '' && is_numeric($salePrice)) {
                $syncData[$priceListId] = [
                    'sale_price' => (float) $salePrice,
                    'tenant_id' => auth()->user()->tenant_id ?? null,
                    'created_by' => auth()->id()
                ];
            }
        }

        if (!empty($syncData)) {
            $product->priceLists()->sync($syncData);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $p = $this->model->findOrFail($id);
            $requestData = $request->all();

            // Inicializar array de datos a actualizar
            $updateData = [];

            // Manejar campos regulares (excepto imagen)
            if (isset($requestData['data'])) {
                // Si viene en formato {data: {campo: valor}}
                foreach ($requestData['data'] as $field => $value) {
                    if ($field !== 'image' && array_key_exists($field, $p->getAttributes())) {
                        $updateData[$field] = $value;
                    }
                }
            } else {
                // Si viene en formato plano {campo: valor}
                foreach ($requestData as $field => $value) {
                    if ($field !== 'image' && array_key_exists($field, $p->getAttributes())) {
                        $updateData[$field] = $value;
                    }
                }
            }

            // Manejar imagen por separado
            if ($request->hasFile('image')) {
                // Eliminar imagen anterior si existe
                if ($p->image) {
                    Storage::delete('public/products/' . $p->image);
                }
                $updateData['image'] = $this->uploadImage($request->file('image'));
            }

            // Manejar campo activo específicamente para asegurar tipo booleano
            if (isset($updateData['active'])) {
                $updateData['active'] = filter_var($updateData['active'], FILTER_VALIDATE_BOOLEAN);
            }

            // Actualizar el modelo solo con los campos proporcionados
            $p->update($updateData);

            $this->syncPriceLists($p, $request);

            DB::commit();

            $tenantId = Auth::user()->tenant_id;

            $cacheKey = "tenant_{$tenantId}_products";

            Cache::forget($cacheKey);

            return $this->successResponse($p->fresh()->load($this->relations));
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return $this->errorResponse($e->getMessage());
        }
    }
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $p = $this->model->findOrFail($id);

            // Eliminar imagen asociada si existe
            if ($p->image) {
                Storage::delete('public/' . $p->image);
            }

            $p->delete();

            DB::commit();

            $tenantId = Auth::user()->tenant_id;

            $cacheKey = "tenant_{$tenantId}_products";

            Cache::forget($cacheKey);

            return $this->successResponse($p);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return $this->errorResponse($e);
        }
    }

    public function changestatus($id)
    {
        try {
            $p = $this->model->findOrFail($id);
            $p->active = $p->active == 1 ? 0 : 1;
            $p->save();

            $tenantId = Auth::user()->tenant_id;

            $cacheKey = "tenant_{$tenantId}_products";

            Cache::forget($cacheKey);

            return $this->successResponse($p->fresh()->load($this->relations));
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    /**
     * Método para subir imágenes
     */
    protected function uploadImage($image)
    {
        // Obtener nombre original y extensión
        $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $image->getClientOriginalExtension();

        // Crear nombre único: nombre_original + uniqid + extensión
        $fileName = $originalName . '_' . uniqid() . '.' . $extension;

        // Guardar en storage/app/public/productos con el nombre personalizado
        $path = $image->storeAs('public/products', $fileName);

        // Retornar solo el nombre del archivo (sin ruta)
        return $fileName;
    }

    /**
     * Métodos adicionales para relaciones
     */
    public function getBrands()
    {
        try {
            $b = Brand::where('active', true)->get();
            return $this->successResponse($b);
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    public function getCategories()
    {
        try {
            $c = Category::where('active', true)->get();
            return $this->successResponse($c);
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            DB::beginTransaction();

            // Obtener parámetros del request
            //$includeInactive = $request->boolean('include_inactive', false);
            //$onlyWithStock = $request->boolean('only_with_stock', false);
            //$onlyWithPriceLists = $request->boolean('only_with_price_lists', false);

            // Crear query base - Asegúrate de cargar los pivots correctamente
            $query = Product::with([
                'brand:id,name',
                'category:id,name',
                'priceLists:id,name', // Ajusta los campos según tu modelo
                'stocks:id,product_id,quantity'
            ]);

            // Filtrar por activos si no se incluyen inactivos
            /*if (!$includeInactive) {
                $query->where('active', true);
            }

            // Filtrar solo productos con stock
            if ($onlyWithStock) {
                $query->whereHas('stocks', function ($q) {
                    $q->where('quantity', '>', 0);
                });
            }

            // Filtrar solo productos con listas de precios
            if ($onlyWithPriceLists) {
                $query->whereHas('priceLists');
            }

            // Aplicar filtros adicionales si existen
            if ($request->has('category_id') && $request->category_id) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->has('brand_id') && $request->brand_id) {
                $query->where('brand_id', $request->brand_id);
            }*/

            // Buscar por código o nombre
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'LIKE', "%{$search}%")
                        ->orWhere('name', 'LIKE', "%{$search}%");
                });
            }

            // Ordenar
            $query->orderBy('id');

            $products = $query->get();

            \Log::info("📊 Exportando {$products->count()} productos");
            \Log::info("Primer producto cargado con relaciones: ", [
                'has_brand' => $products->first() ? $products->first()->relationLoaded('brand') : false,
                'has_priceLists' => $products->first() ? $products->first()->relationLoaded('priceLists') : false,
                'priceLists_count' => $products->first() ? $products->first()->priceLists->count() : 0,
            ]);

            // Verificar si hay datos
            if ($products->isEmpty()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No hay productos para exportar con los filtros aplicados'
                ], 404);
            }

            // Formatear fecha para el nombre del archivo
            $now = \Carbon\Carbon::now()->format('Y-m-d_H-i');
            $fileName = "productos_precios_stock_{$now}";

            // Crear el export pasando los productos
            $export = new ProductsListAndStockExport($products);

            DB::commit();

            return Excel::download($export, $fileName . '.xlsx');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('💥 ERROR en exportExcel: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al generar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview changes from Excel import
     */
    public function importExcelPreview(Request $request)
    {
        // Debug logging to see exactly what's arriving
        \Log::info('📥 Petición de importación recibida', [
            'has_file' => $request->hasFile('file'),
            'file_name' => $request->hasFile('file') ? $request->file('file')->getClientOriginalName() : null,
            'file_mime' => $request->hasFile('file') ? $request->file('file')->getMimeType() : null,
            'all_data' => $request->except('file')
        ]);

        $validator = Validator::make($request->all(), [
            'file' => 'required|max:10240', // Relaxed mime validation
        ]);

        if ($validator->fails()) {
            \Log::warning('⚠️ Fallo de validación en importExcelPreview', ['errors' => $validator->errors()->toArray()]);
            return $this->errorResponse(new \Exception($validator->errors()->first()), 'Error de validación', 422);
        }

        try {
            $file = $request->file('file');
            
            if (!$file || !$file->isValid()) {
                throw new \Exception('El archivo no es válido o no se cargó correctamente');
            }

            $data = Excel::toCollection(new ProductImport, $file);

            if ($data->isEmpty() || $data->first()->isEmpty()) {
                return $this->errorResponse(new \Exception('El archivo está vacío'), 'Archivo vacío', 400);
            }

            $rows = $data->first();
            $previews = [];
            $allPriceLists = PriceList::all();

            foreach ($rows as $row) {
                $productId = $row['id'] ?? null;
                $productCode = $row['codigo'] ?? null;

                $product = null;
                if ($productId) {
                    $product = Product::with('priceLists', 'stocks')->find($productId);
                } elseif ($productCode) {
                    $product = Product::with('priceLists', 'stocks')->where('code', $productCode)->first();
                }

                if (!$product) {
                    continue;
                }

                $newPurchasePrice = $row['precio_compra'] ?? null;
                $newStock = isset($row['stock_a_ingresar']) ? (float)$row['stock_a_ingresar'] : null;
                $currentStock = $product->stocks ? $product->stocks->sum('quantity') : 0;

                $changes = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'old_purchase_price' => $product->purchase_price,
                    'new_purchase_price' => $newPurchasePrice,
                    'old_stock' => $currentStock,
                    'new_stock' => $newStock,
                    'price_lists' => []
                ];

                $hasChanges = false;
                if ($newPurchasePrice != $product->purchase_price) {
                    $hasChanges = true;
                }

                if ($newStock !== null) {
                    $hasChanges = true;
                }

                foreach ($row as $key => $value) {
                    if (strpos($key, 'precio_') === 0) {
                        $priceListName = str_replace('precio_', '', $key);
                        // Clean "Precio: " prefix if exists (mapping from export)
                        $priceListName = trim(str_replace('precio_precio_', '', $key));
                        // The heading row normally replaces spaces with underscores.
                        // We need to match it back to the PriceList name.
                        
                        // Let's find the matching price list by slugifying the name
                        $matchedPriceList = $allPriceLists->first(function($pl) use ($priceListName) {
                           return Str::slug($pl->name, '_') === $priceListName || Str::slug("Precio: ".$pl->name, '_') === $priceListName;
                        });

                        if ($matchedPriceList) {
                            $oldPrice = 0;
                            $pivot = $product->priceLists->where('id', $matchedPriceList->id)->first();
                            if ($pivot) {
                                $oldPrice = $pivot->pivot->sale_price;
                            }

                            if ($value != $oldPrice) {
                                $hasChanges = true;
                                $changes['price_lists'][] = [
                                    'name' => $matchedPriceList->name,
                                    'old_sale_price' => $oldPrice,
                                    'new_sale_price' => $value
                                ];
                            }
                        }
                    }
                }

                if ($hasChanges) {
                    $previews[] = $changes;
                }
            }

            $cacheKey = 'import_preview_' . Auth::id() . '_' . time();
            Cache::put($cacheKey, $previews, 60 * 30); // 30 minutes

            return $this->successResponse([
                'preview_key' => $cacheKey,
                'changes_count' => count($previews),
                'changes' => $previews
            ], 'Vista previa generada correctamente');

        } catch (\Exception $e) {
            \Log::error('Error en importExcelPreview: ' . $e->getMessage());
            return $this->errorResponse($e, 'Error al procesar el archivo');
        }
    }

    /**
     * Confirm and process the import updates (ASYNCHRONOUS)
     */
    public function importExcelConfirm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'preview_key' => 'required|string',
            'file_name' => 'nullable|string',
            'file_size' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(new \Exception($validator->errors()->first()), 'Error de validación', 422);
        }

        $cacheKey = $request->preview_key;
        $updates = Cache::get($cacheKey);

        if (!$updates) {
            return $this->errorResponse(new \Exception('La vista previa ha expirado o no existe'), 'Error de sesión', 400);
        }

        // Create import log
        $importLog = ProductImportLog::create([
            'file_name' => $request->file_name ?? 'Importación de Precios',
            'file_size' => $request->file_size ?? 'N/A',
            'status' => 'pending',
            'total_rows' => count($updates),
            'user_id' => Auth::id(),
            'tenant_id' => Auth::user()->tenant_id,
        ]);

        // Dispatch job asynchronously
        UpdateProductPricesJob::dispatch($updates, $importLog->id);
        
        Cache::forget($cacheKey);

        // Clear products cache
        $tenantId = Auth::user()->tenant_id;
        $cacheKeyProducts = "tenant_{$tenantId}_products";
        Cache::forget($cacheKeyProducts);

        return $this->successResponse($importLog, 'El proceso de actualización ha comenzado en segundo plano');
    }

    /**
     * Get import history
     */
    public function importExcelHistory()
    {
        try {
            $history = ProductImportLog::latest()->take(20)->get();
            return $this->successResponse($history);
        } catch (\Exception $e) {
            return $this->errorResponse($e, 'Error al obtener el historial');
        }
    }
}
