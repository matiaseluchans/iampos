<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class OrderRepository extends BaseRepository
{


    public function __construct(Order $m, array $relations = ['customer', 'status', 'orderType', 'shippingStatus', 'paymentStatus'])
    {
        parent::__construct($m, $relations);
    }

    public function save($request)
    {
        /*try {
            $jsonData = $request->getContent(); // Obtener datos del cuerpo de la solicitud
            $modelData = json_decode($jsonData, true); // Deserializar JSON a array

            dd($modelData);

            // Crear un nuevo modelo a partir de los datos recibidos            
            $this->model->fill($modelData);
            $this->model->save();

            //$this->cacheForget();
            return $this->successResponseCreate($this->model);
        } catch (\Exception $e) {

            report($e);
            DB::rollBack();
            return $this->errorResponse($e);
        }*/


        DB::beginTransaction();

        try {
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
           

            //Datos del Usuario
            $user = Auth::user();
            // Datos del formulario
            $formRequest = $request->all();
            
            $form = $formRequest;
            $items = $formRequest['items'];                   
            // Creación de la instancia del modelo 'order'
            $model = new $this->model;                 

            $model->fill([                
                'order_date'=>Carbon::now(),                
                'customer_id' => $form['customer_id'],
                //'customer_details',
                'shipping_address' => $form['shipping_address'],
                'status_id' => 1,
                'order_type_id' => 1,
                'shipping_status_id' => 1,
                'quantity_products' => $form['quantity_products'],
                'subtotal' => $form['quantity_products'],
                'tax_amount' => $form['subtotal'],
                'discount_amount' => $form['discount_amount'],
                'total_amount' => $form['total_amount'],
                'total_cost' => $form['total_cost'],
                'total_profit' => ($form['total_profit'])??0,
                'payment_status_id' => 1,
                'notes' => $form['notes'],
            ]);

            $model->save();            

            // Registro de items con inserción masiva
            if (!empty($items)) {
                /*
                "items" => array:1 [
                    0 => array:6 [
                    "product_id" => 1
                    "quantity" => 1
                    "unit_price" => 1400
                    "unit_cost_price" => 1000
                    "total_price" => 1400
                    "total_profit" => 400
                    ]
                ]
                */                
            
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
