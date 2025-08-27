<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\PriceListRepository;
use Illuminate\Support\Facades\DB;
use App\Models\PriceList;

class PriceListController extends Controller
{
    private $repository;

    protected $model;

    public function __construct(PriceListRepository $repository)
    {
        $this->model = new PriceList();
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
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

    public function delete($id)
    {
        return $this->repository->delete($id);
    }

    public function changestatus(Request $request, $id)
    {
        return $this->repository->changestatus($request, $id);
    }



    // Método para agregar o actualizar productos en una lista de precios
    public function syncProducts(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $priceList = $this->model->findOrFail($id);
            $products = $request->input('products'); // Array de productos [{product_id: 1, sale_price: 15.50}, ...]

            $syncData = [];
            foreach ($products as $product) {
                $syncData[$product['product_id']] = ['sale_price' => $product['sale_price']];
            }

            $priceList->products()->sync($syncData);

            DB::commit();
            return $this->successResponse($priceList->fresh()->load('products'));
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return $this->errorResponse($e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $priceList = $this->model->findOrFail($id);
            $priceList->delete();
            return $this->successResponse($priceList);
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }
}
