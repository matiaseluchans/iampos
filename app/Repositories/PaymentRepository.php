<?php

namespace App\Repositories;

use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Status;
use App\Models\PaymentStatus;
use App\Enums\StatusEnum;

class PaymentRepository extends BaseRepository
{
    public function __construct(Payment $m)
    {
        parent::__construct($m);
    }

    public function save($request)
    {       
        DB::beginTransaction();
        try {
            
            // Validación de los datos generales del pedido
            $validator = Validator::make($request->all(), [                
                'order_id' => 'required',                
            ], [
                "order_id.required" => getMsg("required"),                
            ]);

            // Validación de los payments
            $validatorItems = Validator::make($request->input('payments', []), [
                '*.payment_method_id' => 'required',
                '*.amount' => 'required',
            ], [
                '*.payment_method_id.required' => getMsg("required"),
                '*.amount.required' => getMsg("required"),
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
            $orderId = $formRequest['order_id'];
            $paymentDate = Carbon::now();
            $payments = $formRequest['payments'];
            $errors = [];                                    

            // Registro de items con inserción masiva
            if (!empty($payments)) {
                $data = array_map(function ($payment) use ($orderId, $paymentDate, $user) {
                    return [
                        'order_id' => $orderId,
                        'payment_method_id' => $payment['payment_method_id']['id'],
                        'amount' => $payment['amount'],
                        'payment_date' => $paymentDate,                        
                        'tenant_id' => $user->tenant_id,
                        'created_by' => $user->id,
                        'created_at' => now(),                        
                    ];
                }, $payments);
                $this->model::insert($data);
            }

            // Calcular total de pagos actuales
            $totalPagado = Payment::where('order_id', $orderId)->sum('amount');
            // Obtener el total de la orden
            $order = Order::findOrFail($orderId);
            // Verificar si se pagó completamente            
            if ((float) $totalPagado >= (float) $order->total_amount) {  
                $statusCode = StatusEnum::PAID;
            }
            else{
                $statusCode = StatusEnum::PARTIAL_PAYMENT;
            }
            $status = Status::where('code', $statusCode)->first();            
            //actualizo el estado de la orden    
            $order->status_id = $status->id;            
            $order->save();

            // Confirmar la transacción
            DB::commit();
            $this->cacheForget();

            return $this->successResponseCreate([                
                'payments' => $this->model::where('order_id', $order)->get(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return $this->errorResponse($e);
        }
    }

}
