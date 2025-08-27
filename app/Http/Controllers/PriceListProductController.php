<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PriceListProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PriceListProductController extends Controller
{
    /**
     * Obtener productos con precios de una lista específica
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'price_list_id' => 'required|exists:price_lists,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validación fallida',
                    'errors' => $validator->errors()
                ], 422);
            }

            $priceListId = $request->price_list_id;

            // Obtener productos con sus precios en la lista especificada
            $products = Product::with(['priceLists' => function ($query) use ($priceListId) {
                $query->where('price_lists.id', $priceListId)
                    ->select('price_lists.id', 'price_list_product.sale_price');
            }])
                ->whereHas('priceLists', function ($query) use ($priceListId) {
                    $query->where('price_lists.id', $priceListId);
                })
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'code' => $product->code,
                        'sale_price' => $product->priceLists->first()->pivot->sale_price ?? $product->sale_price,
                        //'default_price' => $product->sale_price
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $products,
                'message' => 'Productos con precios de la lista obtenidos exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los productos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar o crear precios en una lista
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'price_list_id' => 'required|exists:price_lists,id',
                'products' => 'required|array',
                'products.*.product_id' => 'required|exists:products,id',
                'products.*.sale_price' => 'required|numeric|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validación fallida',
                    'errors' => $validator->errors()
                ], 422);
            }

            $priceListId = $request->price_list_id;
            $products = $request->products;

            foreach ($products as $productData) {
                PriceListProduct::updateOrCreate(
                    [
                        'price_list_id' => $priceListId,
                        'product_id' => $productData['product_id'],
                        'tenant_id' => auth()->user()->tenant_id
                    ],
                    [
                        'sale_price' => $productData['sale_price'],
                        'created_by' => auth()->id(),
                        'last_modified_by' => auth()->id()
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Precios actualizados exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar precios: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener el precio de un producto en una lista específica
     */
    public function getProductPrice($priceListId, $productId): JsonResponse
    {
        try {
            $price = PriceListProduct::where('price_list_id', $priceListId)
                ->where('product_id', $productId)
                ->first();

            if (!$price) {
                // Si no existe precio específico, devolver el precio por defecto del producto
                $product = Product::find($productId);
                return response()->json([
                    'success' => true,
                    'data' => [
                        'sale_price' => $product->sale_price,
                        'is_default' => true
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'sale_price' => $price->sale_price,
                    'is_default' => false
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el precio: ' . $e->getMessage()
            ], 500);
        }
    }
}
