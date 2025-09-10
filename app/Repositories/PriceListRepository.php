<?php

namespace App\Repositories;

use App\Models\PriceList;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PriceListRepository extends BaseRepository
{

    private $relations = ['products'];

    public function __construct(PriceList $m, array $relations = ['products'])
    {
        parent::__construct($m, $relations);
    }


    public function all()
    {
        try {

            $roles = Auth::user()->roles()->get();


            $query = $this->model->query();
            // Si es vendedor de bebidas (rol ID 3), solo ve sus lista de precios
            if ($roles[0]->id == 3) {
                $query->where('id', 2);
            }
            if (!empty($this->relations)) {
                $query = $query->with($this->relations);
            }

            return $this->successResponse($query->get());
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    public function save($data)
    {
        DB::beginTransaction();
        try {
            // Crear la lista de precios
            $priceList = $this->model->create($data);

            // Sincronizar todos los productos con precio 0
            $this->syncAllProductsWithZeroPrice($priceList);

            DB::commit();
            return $this->successResponseCreate($priceList->load('products'));
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return $this->errorResponse($e);
        }
    }

    public function syncAllProductsWithZeroPrice(PriceList $priceList)
    {
        $products = \App\Models\Product::where('tenant_id', $priceList->tenant_id)->get();

        $insertData = [];
        $now = now();
        $user = Auth::user();

        foreach ($products as $product) {
            $insertData[] = [
                'price_list_id' => $priceList->id,
                'product_id' => $product->id,
                'tenant_id' => $user->tenant->id,
                'sale_price' => 0,
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => $user->id
            ];
        }

        if (!empty($insertData)) {
            DB::table('price_list_product')->insert($insertData);
        }
    }


    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $form = $request->all();
            $model = $this->model::with('products')->findOrFail($id);

            // Actualizar los datos básicos de la lista de precios
            $model->update($form['data']);

            // Si hay productos en la solicitud, actualizar sus precios
            if (isset($form["data"]['products']) && is_array($form["data"]['products'])) {
                $this->updateProductPrices($model, $form["data"]['products']);
            }

            DB::commit();
            return $this->successResponse($model->fresh()->load('products'));
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return $this->errorResponse($e);
        }
    }

    private function updateProductPrices(PriceList $priceList, array $productsData)
    {
        $user = Auth::user();
        $now = now();

        $updateData = [];

        foreach ($productsData as $product) {
            // Asegurarse de que el producto tenga pivot data
            if (isset($product['pivot'])) {
                $updateData[$product['id']] = [
                    'sale_price' => $product['pivot']['sale_price'] ?? 0,
                    'updated_at' => $now,
                    'last_modified_by' => $user->id
                ];
            }
        }

        if (!empty($updateData)) {
            $priceList->products()->sync($updateData);
        }
    }
}
