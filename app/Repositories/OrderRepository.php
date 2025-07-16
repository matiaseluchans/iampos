<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class OrderRepository extends BaseRepository
{


    //public function __construct(Order $m, array $relations = ['customer', 'status', 'orderType', 'shippingStatus', 'paymentStatus'])
    public function __construct(Order $m, array $relations = ['customer', 'status', 'orderType'])
    {
        parent::__construct($m, $relations);
    }

    public function save($request)
    {       
        DB::beginTransaction();

        try {
            //Datos del Usuario
            $user = Auth::user();
            // Datos del formulario
            $formRequest = $request->all();
            
            $form = $formRequest;
            $items = $formRequest['items'];
            $errors = [];
            // Validación de los datos generales del pedido
            $validator = Validator::make($request->all(), [                
                'customer_id' => 'required',                
            ], [
                "customer_id.required" => getMsg("required"),                
            ]);

            // Validación de los ítems del pedido
            $validatorItems = Validator::make($request->input('items', []), [
                '*.product_id' => 'required',
                '*.quantity' => 'required',
            ], [
                '*.product_id.required' => getMsg("required"),
                '*.quantity.required' => getMsg("required"),
            ]);

            // Verificación de errores
            if ($validator->fails() || $validatorItems->fails()) {
                $errors = array_merge(
                    $validator->errors()->all(),
                    $validatorItems->errors()->all()
                );

                return $this->errorResponse(null, implode(', ', $errors));
            }           

            foreach ($items as $item) {
                $stock = Stock::where('product_id', $item['product_id'])->first();
                
                if ($stock->quantity < $item['quantity']) {
                    $errors[] = "No hay suficiente stock para el item ID {$item['product_id']}";
                }
            }

            if (count($errors)) {
                return $this->errorResponse(null, implode(', ', $errors));
            }
            
            // Creación de la instancia del modelo 'order'
            $model = new $this->model;   
            $sellerId = '';
            $sellerName = '';            
            if (!$user->roles()->where('name', 'like', '%-admin%')->exists()) {
                $sellerId = $user->id;
                $sellerName = $user->name;
            }
            else{
                $sellerId = $form['seller_id']['id'];
                $sellerName = $form['seller_id']['name'];
            }
//dd($form['delivery_date']);
            $model->fill([                
                'order_date'=>Carbon::now(),                
                'delivery_date' => isset($form['delivery_date'])?  Carbon::createFromFormat('d/m/Y', $form['delivery_date'])->format('Y-m-d') :null,
                'customer_id' => $form['customer_id'],
                //'customer_details', //ver de guardar los datos del cliente
                'shipping_address' => $form['shipping_address'],
                'shipping' => isset($form['shipping'])?$form['shipping']:0,
                'status_id' => 1,
                'order_type_id' => 1,                
                'quantity_products' => $form['quantity_products'],
                'subtotal' => $form['subtotal'],
                'tax_amount' => $form['tax_amount'],
                'discount_amount' => $form['discount_amount'],
                'total_amount' => $form['total_amount'],
                'total_cost' => $form['total_cost'],
                'total_profit' => ($form['total_profit'])??0,                
                'notes' => $form['notes'],
                'seller_id' => $sellerId,
                'seller_name' => $sellerName,
            ]);

            $model->save();            

            // Registro de items con inserción masiva
            if (!empty($items)) {              
                $data = array_map(function ($item) use ($model, $user) {
                    return [
                        'order_id' => $model->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'unit_cost_price' => $item['unit_cost_price'],
                        'total_profit' => $item['total_profit']??0,
                        'total_price' => $item['total_price'],
                        'tenant_id' => $user->tenant_id,
                        'created_by' => $user->id,
                        'created_at' => now(),                        
                    ];
                }, $items);
                OrderItem::insert($data);

                //tengo q descontar del stock las cantidades de los items
                $ids = collect($items)->pluck('product_id')->toArray();

                //creo un update masivo
                $sql = "UPDATE stocks SET quantity = CASE product_id ";
                foreach ($items as $item) {
                    $sql .= "WHEN {$item['product_id']} THEN quantity - {$item['quantity']} ";
                }
                $sql .= "END WHERE product_id IN (" . implode(',', $ids) . ")";
                
                DB::statement($sql);                
            }
            // Confirmar la transacción
            DB::commit();
            $this->cacheForget();

            return $this->successResponseCreate([
                'order' => $model,                
                'items' => OrderItem::where('order_id', $model->id)->get(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return $this->errorResponse($e);
        }
    }

}
