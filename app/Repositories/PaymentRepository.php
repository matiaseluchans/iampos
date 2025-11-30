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
    public function __construct(Payment $m, array $relations = ["paymentMethod"])
    {
        parent::__construct($m, $relations);
    }

    public function save($request)
    {
        DB::beginTransaction();
        try {
            // [Validaciones anteriores...]

            // Datos del Usuario
            $user = Auth::user();
            $formRequest = $request->all();

            $orderId = $formRequest['order_id'];
            $paymentDate = Carbon::now();
            $payments = $formRequest['payments'];

            // Calcular total de nuevos pagos
            $newPaymentsTotal = array_sum(array_column($payments, 'amount'));

            // Obtener datos existentes
            $previousTotalPaid = Payment::where('order_id', $orderId)->sum('amount');
            $order = Order::findOrFail($orderId);

            // Calcular total después de nuevos pagos y cambio
            $totalAfterNewPayments = $previousTotalPaid + $newPaymentsTotal;
            $changeAmount = max(0, $totalAfterNewPayments - $order->total_amount);

            // Registrar pagos principales
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

            // Registrar cambio si es necesario
            $finalTotalPaid = $totalAfterNewPayments;
            if ($changeAmount > 0) {
                // Buscar método de pago en efectivo para el cambio, o usar el primero
                $changePaymentMethodId = $this->getChangePaymentMethodId($payments);

                $changePayment = [
                    'order_id' => $orderId,
                    'payment_method_id' => $changePaymentMethodId,
                    'amount' => -$changeAmount,
                    'payment_date' => $paymentDate,
                    'tenant_id' => $user->tenant_id,
                    'created_by' => $user->id,
                    'created_at' => now(),
                    'notes' => 'Cambio'
                ];

                $this->model::create($changePayment);
                $finalTotalPaid = $totalAfterNewPayments - $changeAmount;
            }

            // Actualizar estado de la orden
            $this->updateOrderPaymentStatus($order, $finalTotalPaid);

            DB::commit();
            $this->cacheForget();

            return $this->successResponseCreate([
                'payments' => $this->model::where('order_id', $orderId)->get(),
                'change_amount' => $changeAmount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return $this->errorResponse($e);
        }
    }

    /**
     * Obtiene el método de pago para el cambio
     * Prioriza métodos en efectivo
     */
    private function getChangePaymentMethodId($payments)
    {
        $user = Auth::user();
        //bebidas
        $defaultPaymentMethod = 1;

        if ($user->tenant_id == 3) { //pet shop
            $defaultPaymentMethod = 6;
        }


        // Si no hay efectivo, usar el primer método de pago
        return $defaultPaymentMethod;
    }

    /**
     * Actualiza el estado de pago de la orden
     */
    private function updateOrderPaymentStatus($order, $totalPaid)
    {
        if ((float) $totalPaid >= (float) $order->total_amount) {
            $statusCode = StatusEnum::PAID;
        } else if ($totalPaid == 0) {
            $statusCode = StatusEnum::REFUND;
        } else {
            $statusCode = StatusEnum::PARTIAL_PAYMENT;
        }

        $paymentStatus = PaymentStatus::where('code', $statusCode)->first();

        $order->total_paid = $totalPaid;
        $order->payment_status_id = $paymentStatus->id;
        $order->save();
    }
}
