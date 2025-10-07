<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentService
{
    /**
     * Query base para el resumen de pagos
     */
    public function getPaymentsSummaryQuery($filters = [])
    {
        $query = Payment::join('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->join('customers', 'orders.customer_id', '=', 'customers.id')
            ->select([
                DB::raw('DATE(payments.payment_date) as date'),
                'payments.payment_method_id',
                'payment_methods.name as payment_method_name',
                DB::raw('SUM(payments.amount) as amount'),
                DB::raw('COUNT(payments.id) as payment_count')
            ])
            ->where('payments.amount', '>', 0);

        // Aplicar filtro de métodos de pago
        if (!empty($filters['payment_methods'])) {
            $query->whereIn('payments.payment_method_id', $filters['payment_methods']);
        }

        return $query->groupBy('date', 'payments.payment_method_id', 'payment_methods.name',)
            ->orderBy('date', 'desc')
            ->orderBy('amount', 'desc');
    }

    /**
     * Obtener detalle de pagos para una fecha y método específico
     */
    public function getPaymentDetails($date, $paymentMethodId)
    {
        return Payment::with(['order.customer', 'paymentMethod'])
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->join('customers', 'orders.customer_id', '=', 'customers.id')
            ->join('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')
            ->whereDate('payments.payment_date', $date)
            ->where('payments.payment_method_id', $paymentMethodId)
            ->where('payments.amount', '>', 0)
            ->select([
                'payments.*',
                'orders.order_number',
                DB::raw("CONCAT(customers.address, ', ', customers.firstname) as customer_name"),
                DB::raw("COALESCE(customers.telephone, 'Sin contacto') as customer_contact")
            ])
            ->orderBy('payments.payment_date', 'desc')
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'payment_date' => $payment->payment_date,
                    'order_number' => $payment->order_number,
                    'customer_name' => $payment->customer_name,
                    'customer_contact' => $payment->customer_contact,
                    'amount' => $payment->amount,
                    'payment_method' => $payment->paymentMethod->name,
                ];
            });
    }

    /**
     * Obtener métodos de pago disponibles
     */
    public function getPaymentMethods()
    {
        return \App\Models\PaymentMethod::active()
            ->select('id', 'name')
            ->get();
    }
}
